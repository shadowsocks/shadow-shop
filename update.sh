#!/bin/sh
TIME=$(date +%Y-%m-%d-%H-%M)
PWD=$1
DOCKER_COMPOSE_COMMAND=$2
cd "$PWD" || exit

if [ ! -f "update.sh" ]
then
    echo "$TIME update.sh is not found."
    echo "$TIME update.sh is not found." >> update.log
    exit 1
fi

if [ ! -f ".env" ]
then
    echo "$TIME .env is not found."
    echo "$TIME .env is not found." >> update.log
    exit 2
fi

# set environment variables from .env
rm -rf .env.tmp
echo "#!/bin/sh" > .env.tmp
sed -E -n 's/[^#]+/export &/ p' .env >> .env.tmp
. ./.env.tmp
rm -rf .env.tmp

# Backup website directory
SOURCE_DIR="$PWD/website"
WEBSITE_BACKUP_FILE_NAME="$PWD/backup/website/website-$TIME.tar.gz"
tar --create --gzip --file="$WEBSITE_BACKUP_FILE_NAME" "$SOURCE_DIR"

# check docker services status
LINE_NUMBER_A=$(docker ps --quiet --no-trunc | wc --lines )
LINE_NUMBER_B=$("$DOCKER_COMPOSE_COMMAND" ps --quiet | wc --lines )
if [ "$LINE_NUMBER_A" -eq 4 ] && [ "$LINE_NUMBER_B" -eq 4 ]
then
    DOCKER_STATUS="running"
elif [ "$LINE_NUMBER_A" -eq 0 ] && [ "$LINE_NUMBER_B" -eq 4 ]
then 
    DOCKER_STATUS="stopped"
elif [ "$LINE_NUMBER_A" -eq 0 ] && [ "$LINE_NUMBER_B" -eq 0 ]
then
    DOCKER_STATUS="down"
else
    DOCKER_STATUS="unknown"
    echo "DOCKER_STATUS=unknown" >> update.log
fi

# Backup databases
DATABASE_BACKUP_FILE_NAME="$PWD/backup/database/database-$TIME.sql"
if [ "$DOCKER_STATUS" = "running" ]
then 
    "$DOCKER_COMPOSE_COMMAND" exec -T db bash -c "echo $'[client]\nuser=root\npassword=$MYSQL_PASSWORD\n' > /credentials.cnf"
    "$DOCKER_COMPOSE_COMMAND" exec -T db mysqldump --defaults-extra-file=/credentials.cnf wordpress > "$DATABASE_BACKUP_FILE_NAME"
    "$DOCKER_COMPOSE_COMMAND" exec -T db rm /credentials.cnf

    # if file has 0 size
    if [ ! -s "$DATABASE_BACKUP_FILE_NAME" ]
    then
        echo "$TIME $DATABASE_BACKUP_FILE_NAME has 0 size"
        echo "$TIME $DATABASE_BACKUP_FILE_NAME has 0 size" >> update.log
    fi

    # compress
    gzip "$DATABASE_BACKUP_FILE_NAME"
    COMPRESSED_BACKUP_FILE_NAME="$DATABASE_BACKUP_FILE_NAME.gz"

    # copy latest backup files
    DATABASE_FILE_NAME="$PWD/backup/database/database_backup.sql.gz"
    rm -rf "$DATABASE_FILE_NAME"
    touch "$DATABASE_FILE_NAME"
    cp "$COMPRESSED_BACKUP_FILE_NAME" "$DATABASE_FILE_NAME"
fi

# Delete backup files olders than 7 days
find "$PWD"/backup/website/ -type f -mtime +7 -name "*.gz" -exec rm {} \;
find "$PWD"/backup/database/ -type f -mtime +7 -name "*.gz" -exec rm {} \;

# Check remote repository for new update
GIT_CHECK_RETURN=$(git ls-remote origin -h refs/heads/master)
EXIT_CODE=$?
if [ $EXIT_CODE -gt 0 ]
then
    echo "$TIME git check remote last commit failed"
    echo "$TIME git check remote last commit failed" >> update.log
    exit 3
fi
REMOTE_HASH=$(echo "$GIT_CHECK_RETURN" | awk 'NR==1{print $1}')
echo "REMOTE_HASH = $REMOTE_HASH"
LOCAL_HASH=$(git rev-parse master)
echo "LOCAL_HASH = $LOCAL_HASH"

if [ "$REMOTE_HASH" = "$LOCAL_HASH" ]
then
    echo "no update from remote"
    exit 0
fi

# Update from remote repository
OLD_HASH=$(md5sum docker-compose.yml | awk 'NR==1{print $1}')
echo "OLD_HASH = $OLD_HASH"
git fetch origin master
EXIT_CODE=$?
if [ $EXIT_CODE -gt 0 ]
then
    echo "$TIME git fetch origin master failed"
    echo "$TIME git fetch origin master failed" >> update.log
    exit 4
fi

git stash  # hide local change before git merge

git merge --strategy-option theirs origin/master # git merge favouring origin/mster
EXIT_CODE=$?
if [ $EXIT_CODE -gt 0 ]
then
    echo "$TIME git merge --strategy-option theirs origin/master failed"
    echo "$TIME git merge --strategy-option theirs origin/master failed" >> update.log
    exit 5
fi

git stash pop
EXIT_CODE=$?
if [ $EXIT_CODE -gt 0 ]
then
    git reset --hard HEAD  # discard all local changes
    git stash drop
fi

# rebuild docker images if docker-compose.yml is upated
NEW_HASH=$(md5sum docker-compose.yml | awk 'NR==1{print $1}')
echo "NEW_HASH = $NEW_HASH"
if [ "$NEW_HASH" = "$OLD_HASH" ]
then 
    echo "update successful"
    exit 0
fi

# pull new images and re-create new containers
if [ "$DOCKER_STATUS" = "running" ] || [ "$DOCKER_STATUS" = "stopped" ]
then
    docker volume prune --force
    "$DOCKER_COMPOSE_COMMAND" down
    EXIT_CODE=$?
    if [ $EXIT_CODE -gt 0 ]
    then
        echo "$TIME docker-compose down exited with non-zero code"
        echo "$TIME docker-compose down exited with non-zero code" >> update.log
    fi

    "$DOCKER_COMPOSE_COMMAND" rm --force
    EXIT_CODE=$?
    if [ $EXIT_CODE -gt 0 ]
    then
        echo "$TIME docker-compose rm --force exited with non-zero code"
        echo "$TIME docker-compose rm --force exited with non-zero code" >> update.log
    fi
fi

docker image prune --force
EXIT_CODE=$?
if [ $EXIT_CODE -gt 0 ]
then
    echo "$TIME docker image prune --all --force exited with non-zero code"
    echo "$TIME docker image prune --all --force exited with non-zero code" >> update.log
fi

if [ "$DOCKER_STATUS" = "running" ] || [ "$DOCKER_STATUS" = "stopped" ]
then
    "$DOCKER_COMPOSE_COMMAND" up --no-start
    EXIT_CODE=$?
    if [ $EXIT_CODE -gt 0 ]
    then
        echo "$TIME docker-compose up --no-start exited with non-zero code"
        echo "$TIME docker-compose up --no-start exited with non-zero code" >> update.log
    fi

    if [ "$DOCKER_STATUS" = "running" ]
    then
        "$DOCKER_COMPOSE_COMMAND" up --detach
        EXIT_CODE=$?
        if [ $EXIT_CODE -gt 0 ]
        then
            echo "$TIME docker-compose up --detach exited with non-zero code"
            echo "$TIME docker-compose up --detach exited with non-zero code" >> update.log
        fi
        echo "successfully updated docker images and re-started services"
        exit 0 # normal
    fi
fi

if [ "$DOCKER_STATUS" = "stopped" ]
then
    echo "successfully updated docker images and left services in stopped stage"
    exit 0 # normal
elif [ "$DOCKER_STATUS" = "down" ]
then
    echo "successfully deleted dangling docker images. New images will be created upon next service creation"
    exit 0 # normal
else # "$DOCKER_STATUS" = "unknown"
    echo "Error! Unknown docker services status"
    echo "Error! Unknown docker services status" >> update.log
    exit 6
fi
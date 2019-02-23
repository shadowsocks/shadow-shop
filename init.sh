#!/bin/sh

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'    # no color

# Check if a command exist
check_command() {
    if ! command -v "$1" > /dev/null 2>&1
    then
        /bin/echo -e "${RED}Command ${GREEN} $1 ${RED} is not found. Please install it. Abort.${NC}"
    exit 1
    fi
}

check_command whoami
check_command curl
check_command awk
check_command sed
check_command wc
check_command cat
check_command tr
check_command fold
check_command head
check_command crontab
check_command grep
check_command tar
check_command gzip
check_command touch
check_command find
check_command git
check_command chown

WHOAMI=$(whoami)
if [ "$WHOAMI" != "root" ]
then
    /bin/echo -e "${RED}Permission denied${NC}"
    exit 2
fi

if [ ! -f "init.sh" ]
then
    /bin/echo -e "${RED}Error! Please run this command within the directory where init.sh resides.${NC}"
    exit 3
fi

# get the original username before sudo
ME=$(logname)

if ! command -v docker > /dev/null 2>&1
then
    # Install docker
    curl -fsSL https://get.docker.com -o get-docker.sh
    EXIT_CODE=$?
    if [ $EXIT_CODE -gt 0 ]
    then
        /bin/echo -e "${RED}Downloading Docker installing script failed. Install Docker manually before running this script again. ${NC}"
        exit 4
    fi
    sudo sh get-docker.sh
    EXIT_CODE=$?
    if [ $EXIT_CODE -gt 0 ]
    then
        /bin/echo -e "${RED}Installing Docker failed. Install it manually before running this script again. ${NC}"
        exit 5
    fi
    rm -f get-docker.sh
    /bin/echo -e "${GREEN}Docker installed${NC}"
else
    DOCKER_VERSION=$(docker --version | awk 'NR==1{print $3}')
    DOCKER_MAJOR_VERSION=$(echo "$DOCKER_VERSION" | sed 's/[^0-9]/ /g' | awk 'NR==1{print $1}')
    DOCKER_MINOR_VERSION=$(echo "$DOCKER_VERSION" | sed 's/[^0-9]/ /g' | awk 'NR==1{print $2}')
    DOCKER_PATCH_VERSION=$(echo "$DOCKER_VERSION" | sed 's/[^0-9]/ /g' | awk 'NR==1{print $3}')
    if [ "$DOCKER_MAJOR_VERSION" -gt 17 ]
    then
        DOCKER_VERSION_OK="yes"
    elif [ "$DOCKER_MAJOR_VERSION" -lt 17 ]
    then
        DOCKER_VERSION_OK="no"
    else #  "$DOCKER_MAJOR_VERSION" -eq 17
        if [ "$DOCKER_MINOR_VERSION" -ge 6 ]
        then
            DOCKER_VERSION_OK="yes"
        else
            DOCKER_VERSION_OK="no"
        fi
    fi

    if [ "$DOCKER_VERSION_OK" = "no" ]
    then
        /bin/echo -e "${RED}Your docker version is too low. Please install or update to the latest version.${NC}"
    fi
fi

if ! command -v docker-compose > /dev/null 2>&1
then
    # Install docker-compose
    sudo curl -L "https://github.com/docker/compose/releases/download/1.23.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/bin/docker-compose
    EXIT_CODE=$?
    if [ $EXIT_CODE -gt 0 ]
    then
        /bin/echo -e "${RED}Downloading docker-compose failed. Install docker-compose manually before running this script again. ${NC}"
        exit 6
    fi
    sudo chmod +x /usr/bin/docker-compose
    /bin/echo -e "${GREEN}docker-compose installed${NC}"
else
    COMPOSE_VERSION=$(docker-compose --version | awk 'NR==1{print $3}')
    COMPOSE_MAJOR_VERSION=$(echo "$COMPOSE_VERSION" | sed 's/[^0-9]/ /g' | awk 'NR==1{print $1}')
    COMPOSE_MINOR_VERSION=$(echo "$COMPOSE_VERSION" | sed 's/[^0-9]/ /g' | awk 'NR==1{print $2}')
    COMPOSE_PATCH_VERSION=$(echo "$COMPOSE_VERSION" | sed 's/[^0-9]/ /g' | awk 'NR==1{print $3}')
    if [ "$COMPOSE_MAJOR_VERSION" -gt 1 ]
    then
        COMPOSE_VERSION_OK="yes"
    elif [ "$COMPOSE_MAJOR_VERSION" -lt 1 ]
    then
        COMPOSE_VERSION_OK="no"
    else #  "$COMPOSE_MAJOR_VERSION" -eq 1
        if [ "$COMPOSE_MINOR_VERSION" -ge 14 ]
        then
            COMPOSE_VERSION_OK="yes"
        else
            COMPOSE_VERSION_OK="no"
        fi
    fi

    if [ "$COMPOSE_VERSION_OK" = "no" ]
    then
        /bin/echo -e "${RED}Your docker-compose version is too low. Please install or update to the latest version.${NC}"
    fi
fi

if [ -f ".env" ]
then
    . ./.env
fi

if [ "$STAGE" = "local" ]
then
    LAST_CHOICE="Development"
elif [ "$STAGE" = "production" ]
then
    LAST_CHOICE="Production"
fi

if [ -z "$LAST_CHOICE" ]
then
    echo "Please select website stage:"
else
    echo "Please select website stage: (your last choice was ${GREEN}$LAST_CHOICE${NC})"
fi

echo "\t\t\t d. Development"
echo "\t\t\t p. Production"

while [ "$SELECTED" != "d" ] || [ "$SELECTED" != "D" ] || [ "$SELECTED" != "p" ] || [ "$SELECTED" != "P" ]
do
    read SELECTED
    case "$SELECTED" in
        d|D)
            STAGE="local"
            /bin/echo -e "Your choice is: ${GREEN}Development${NC}"
            break
        ;;
        p|P)
            STAGE="production"
            /bin/echo -e "You choice is: ${GREEN}Production${NC}"
            break
        ;;
        *) 
            /bin/echo -e "${RED}Invalid selection! Please re-select a vlid option: ${NC}"
    esac
done

if [ -z "$DOMAIN_NAME" ]
then
    echo "Please input the domain name that you want to use:"
else
    echo "Please input the domain name that you want to use: (your last input was ${GREEN}$DOMAIN_NAME${NC})"
fi

read DOMAIN_NAME
if [ "$STAGE" = "production" ] 
then
    GETENT_RESULT=$(getent hosts "$DOMAIN_NAME" | wc --lines)
    if [ "$GETENT_RESULT" -eq 0 ]
    then
        /bin/echo -e "${RED}The domain name you provided seems wrong${NC}"
        /bin/echo -e "${RED}Are you sure to continue? (y/n)${NC}"
        read IF_CONTINUE
        if [ "$IF_CONTINUE" != "y" ] && [ "$IF_CONTINUE" != "Y" ]
        then
            /bin/echo -e "${RED}Abort.${NC}"
            exit 7
        fi
    fi
fi

# generate mysql password only if it has not been generated before
if [ -z "$MYSQL_PASSWORD" ]
then
    MYSQL_PASSWORD=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
fi

# create environment variable file .env
rm -rf .env
echo "DOMAIN_NAME=$DOMAIN_NAME" >> .env
echo "STAGE=$STAGE" >> .env
echo "MYSQL_PASSWORD=$MYSQL_PASSWORD" >> .env

RANDOM=$$
MINUTE=$((RANDOM%60))
HOUR=$((RANDOM%24))

PWD=$(pwd)
DOCKER_COMPOSE_COMMAND=$(command -v docker-compose)
CRON_UNIQUE_PATTERN="* * * sh $PWD/update.sh $PWD $DOCKER_COMPOSE_COMMAND"

crontab_exists() {
    sudo -u "$1" crontab -l 2>/dev/null | grep -w "$CRON_UNIQUE_PATTERN" >/dev/null 2>/dev/null
}

if ! crontab_exists "$ME"
then
    sudo -u "$ME" crontab -l > mycron 2>/dev/null
    echo "$MINUTE $HOUR $CRON_UNIQUE_PATTERN" >> mycron
    sudo -u "$ME" crontab mycron
    rm mycron
fi

sudo chown www-data:www-data website/ --recursive
# adding the user to docker group
sudo usermod -aG docker "$ME"
# adding $ME user to www-data group and vice-versa
sudo usermod -aG www-data "$ME"
sudo usermod -aG "$ME" www-data

sudo chmod g+w website/ --recursive

if [ "$ME" != "root" ]
then
    sudo chmod g-w "/home/$ME"
    if [ -d "/home/$ME/.ssh" ]
    then
        sudo chmod 700 "/home/$ME/.ssh"
        if [ -f "/home/$ME/.ssh/authorized_keys" ]
        then
            sudo chmod 600 "/home/$ME/.ssh/authorized_keys"
        fi
    fi
else
    sudo chmod g-w "/root"
    if [ -d "/root/.ssh" ]
    then
        sudo chmod 700 "/root/.ssh"
        if [ -f "/root/.ssh/authorized_keys" ]
        then
            sudo chmod 600 "/root/.ssh/authorized_keys"
        fi
    fi
fi

/bin/echo -e "${GREEN}Sucess!${NC}"
/bin/echo -e "Please restart the server and login as ${GREEN} ${ME} ${NC}"
exit 0
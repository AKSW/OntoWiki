#!/bin/bash

# get real directory of this script
CSSOURCE="${BASH_SOURCE[0]}"
while [ -h "$CSSOURCE" ] ; do CSSOURCE="$(readlink "$CSSOURCE")"; done
CSDIR="$( cd -P "$( dirname "$CSSOURCE" )" && pwd )"

# load config file
source $CSDIR/config;

# function install the Codesniffer Pear Package and enable the Codesniffer
function cs-install() {
    return_value=0
    cs=`pear info PHP_CodeSniffer`;
    if [[ $cs == "No information found for \`PHP_CodeSniffer'" ]]
    then
        user=`whoami`;
        if [[ $user == "root" ]]
        then
            pear install PHP_CodeSniffer;
            echo " CodeSniffer PEAR Package installed.";
            return_value=0
        else
            echo " You need root privileges. Please run 'sudo make cs-install'.";
            return_value=1
        fi
    else
        echo " CodeSniffer PEAR Package already installed.";
        return_value=1
    fi
    return $return_value;
}

# function uninstall the Codesniffer Pear Package and disable the Codesniffer
function cs-uninstall() {
    return_value=0
    cs=`pear info PHP_CodeSniffer`;
    if [[ $cs != "No information found for \`PHP_CodeSniffer'" ]]
    then
        user=`whoami`;
        if [[ $user == "root" ]]
        then
            echo -n " Do you really want to uninstall PHP_CodeSniffer Package? (y/n): "
            read CONFIRM;
            if [[ $CONFIRM == "y" ]]
            then
                pear uninstall PHP_CodeSniffer;
                echo " CodeSniffer PEAR Package uninstalled.";
                return_value=0
            else
                echo " CodeSniffer PEAR Package not uninstalled.";
                return_value=1
            fi
        else
            echo " You need root privileges. Please run 'sudo make cs-uninstall'.";
            return_value=1
        fi
    else
        echo " CodeSniffer PEAR Package not uninstalled, because CodeSniffer PEAR Package was not installed.";
        return_value=1
    fi
    return $return_value;
}

# function enables the CodeSniffer pre-commit
function cs-enable() {
    if `ln -s "../../$CSPATH/pre-commit" .git/hooks/pre-commit`
    then
        echo " CodeSniffer pre-commit enabled.";
    else
        echo " CodeSniffer pre-commit already enabled.";
    fi
}

# function disables the CodeSniffer pre-commit
function cs-disable() {
    if `rm .git/hooks/pre-commit`
    then
        echo " CodeSniffer pre-commit disabled.";
    else
        echo " CodeSniffer pre-commit already disabled.";
    fi
}

# function install the CodeSniffer Makefile-functions and pre-commit
# on a submodule
function cs-install-submodule() {
    return_value=0
    LASTLETTER=${1: -1}
    if  [[ $LASTLETTER != "/" ]]
    then
        MPATH=$1/
    else
        MPATH=$1
    fi
    BACKMPATH=${MPATH//[^\/]}
    BACKMPATH=${BACKMPATH//\//..\/}
    cs-install;
    if `ln -s $BACKMPATH$CSPATH"Makefile" $MPATH"Makefile"`;
    then
        echo " CodeSniffer installed for Submodule '$MPATH'.";
        return_value=0
    else
        echo " Can't install CodeSniffer for Submodule '$MPATH' because Makefile already exists.";
        echo " Maybe CodeSniffer already installed!?";
        return_value=1
    fi
    return $return_value;
}
# function uninstall the CodeSniffer Makefile-functions and pre-commit
# on a submodule
function cs-uninstall-submodule() {
    return_value=0
    LASTLETTER=${1: -1}
    if  [[ $LASTLETTER != "/" ]]
    then
        MPATH=$1/
    else
        MPATH=$1
    fi
    echo -n " Do you really want to uninstall CodeSniffer for Submodule '$MPATH'? (y/n): "
    read CONFIRM;
    if [[ $CONFIRM == "y" ]]
    then
        rm "$MPATH"Makefile
        echo " CodeSniffer uninstalled for Submodule '$MPATH'.";
        return_value=0
    else
        echo " CodeSniffer not uninstalled for Submodule '$MPATH'.";
        return_value=1
    fi
    return $return_value;
}

# function run a CodeSniffer Check on specific files
function cs-check() {
    phpcs --extensions=$FILETYPES --severity=$SEVERITY -p --standard=$CSDIR/$CSPATH $1
}

# function run the CodeSniffer pre-commit
function cs-check-precommit() {
    $CSDIR/pre-commit $1
}

# this loop parse the paramter for this script
while getopts ":iuedc:sp:m:f:n:" optname
    do
        case "$optname" in
        "i")
            if cs-install
            then
                cs-enable;
            fi
        ;;
        "u")
            if cs-uninstall
            then
                cs-disable;
            fi
        ;;
        "e")
            cs-enable;
        ;;
        "d")
            cs-disable;
        ;;
        "m")
            if cs-install-submodule "$OPTARG"
            then
               cd "$OPTARG";
               make cs-enable;
            fi
        ;;
        "n")
            TEMPPATH=$(pwd)
            cd "$OPTARG";
            make cs-disable;
            cd $TEMPPATH
            cs-uninstall-submodule "$OPTARG"
        ;;
        "c")
            cs-check "$OPTARG";
        ;;
        "p")
            cs-check-precommit "$OPTARG";
        ;;
        "f")
            CSPATH="$OPTARG";
        ;;
        "s")
            SEVERITY=$SEVERITY_INTENSIVE;
        ;;
        "?")
            echo "Unknown option $OPTARG"
        ;;
        ":")
            echo "No argument value for option $OPTARG"
        ;;
        *)
            # Should not occur
            echo "Unknown error while processing options"
        ;;
    esac
done

exit 0;

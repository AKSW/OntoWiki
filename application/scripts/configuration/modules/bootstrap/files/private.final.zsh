# start a screen/tmux session if not already in it
if [[ "$TERM" =~ "^screen.*$" ]]; then
    # I'm already inside of a screen
else
    byobu -r || byobu && clear && exit
fi

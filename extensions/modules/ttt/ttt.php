<?php

class TttModule extends OntoWiki_Module
{
    public function getContents()
    {
        return '<code>' . $this->_privateConfig->foo . '</code>';
    }
}


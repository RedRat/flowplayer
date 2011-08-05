<?php

/**
 * flowplayer: Allows to embed a flash video into Wiki pages
 *
 * This plugin is based on the flowplay2 plugin by bspot and flashplayer plugin by Arno Welzel
 *
 * Syntax:
 *   {{flowplayer>video.flv [width,height] [[no]param] [param:value]}}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Alexey Markov <redrat@mail.ru>
 * @version    0.2
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
define('FLOWPLAYER', DOKU_BASE.'lib/plugins/flowplayer/player/flowplayer-3.2.7.swf');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_flowplayer extends DokuWiki_Syntax_Plugin {

    function getInfo() {
        return array(
            'author' => 'Alexey Markov',
            'email'  => 'redrat@mail.ru',
            'date'   => '2010-08-04',
            'version'=> '0.2',
            'name'   => 'flowplayer',
            'desc'   => 'Allows to embed a flash video into Wiki pages',
            'url'    => 'http://wiki.splitbrain.org/plugin:flowplayer',
            'ask'    => 'This plugin is based on the flowplay2 plugin by bspot and flashplayer plugin by Arno Welzel',
        );
    }

    function getType() { return 'substition'; }
    function getSort() { return 32; }

    function connectTo($mode) { $this->Lexer->addSpecialPattern('{{flowplayer>.*?}}',$mode,'plugin_flowplayer'); }

    function handle($match, $state, $pos, &$handler) {
        $width = 320;
        $height = 240;
        $params['scaling'] = '"fit"';
        $params['autoPlay'] = 'false';
        $params['urlEncoding'] = 'true';
        list($url, $attr) = explode(" ", substr($match, 13, -2), 2);
        foreach (explode(" ", $attr) as $param) {
            if (preg_match('/(\d+),(\d+)/', $param, $res)) {
                $width = intval($res[1]);
                $height = intval($res[2]);
            }
            else if (preg_match('/([^:]+):(.*)$/', $param, $res))
                $params[strtolower(substr($res[1], 0, 1)).substr($res[1], 1)] = '"'.$res[2].'"';
            else if (substr($param, 0, 2) == "no")
                $params[strtolower(substr($param, 2, 1)).substr($param, 3)] = 'false';
            else
                $params[strtolower(substr($param, 0, 1)).substr($param, 1)] = 'true';
        }
        if (strpos($url,'://')===false) { $url = ml($url); }
        return array('url' => $url, 'width' => $width, 'height' => $height, 'attr' => $params);
    }

    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
            $renderer->doc .= '<object id="flowplayer" type="application/x-shockwave-flash"';
            $renderer->doc .= ' data="'.FLOWPLAYER.'"';
            $renderer->doc .= ' width="'.$data['width'].'"';
            $renderer->doc .= ' height="'.$data['height'].'"';
            $renderer->doc .= '>';
            $renderer->doc .= '<param name="movie" value="'.FLOWPLAYER.'" />';
            $renderer->doc .= '<param name="allowfullscreen" value="true" />';
            $renderer->doc .= '<param name="flashvars" value='."'";
            $renderer->doc .= 'config={"clip":{"url":"'.$data['url'].'"';
            foreach ($data['attr'] as $key => $value) {
                $renderer->doc .= ',"'.$key.'":'.$value;
            }
            $renderer->doc .= "}}'".' />';
            $renderer->doc .= '<b>'.$this->getLang('GetFlash').'</b>';
            $renderer->doc .= '</object>';
            return true;
        }
        return false;
    }
}

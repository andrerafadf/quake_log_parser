<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ParserController extends Controller
{
    public $arLog = [];



    public function readLogFile($nameLogFile = 'games.log')
    {
        $counterGame = 1;
        $arFile = file(storage_path('app/public/'.$nameLogFile));
        foreach($arFile as $line){

            if($this->isIgnoreLine($line)) continue;

            if($this->isInitGame($line)){
                $game = new \StdClass();
                $game->total_kills = 0;
                $game->players = [];
                $game->kills = new \StdClass();
            }

            if($this->isPlayer($line)){
                $player = $this->getPlayer($line);

                if(!in_array($player, $game->players)){
                    $game->players[] = $player;
                }

                $game->kills->$player = 0;
            }

            if($this->isKill($line)){
                $game->total_kills++;
                $kill = $this->getKill($line);

                if($kill){
                    $game->kills->$kill += 1;
                }
            }

            if($this->isShutdownGame($line)){
                $this->arLog['game_'.$counterGame] = $game;
                $counterGame++;
            }
        }
    }

    private function isIgnoreLine($line)
    {
        if(preg_match('/------------------------------------------------------------/', $line)){
            return true;
        }
    }

    private function isInitGame($line)
    {
        if(preg_match('/InitGame/i', $line)){
            return true;
        }
    }

    private function isPlayer($line)
    {
        if(preg_match('/n\\\\(\D+)\\\\t/i', $line)){
            return true;
        }
    }

    private function getPlayer($line)
    {
        preg_match('/n\\\\(\D+)\\\\t/i', $line, $result);
        return $result[1];
    }

    private function isShutdownGame($line)
    {
        if(preg_match('/ShutdownGame/i', $line)){
            return true;
        }
    }

    private function isKill($line)
    {
        if(preg_match('/Kill/i', $line)){
            return true;
        }
    }

    private function getKill($line)
    {
        $killer = '';

        preg_match('/(\S+)(\D+)(\S+\s+\S+\s+\d+):\s(\D+)(\s+killed)/i',$line, $result);

        if($result[4] != '<world>'){
            $killer = $result[4];
        }

        return $killer;
    }
}

<?php

/*
 * Copyright (c) 2021 PJZ9n.
 *
 * This file is part of MorseChat.
 *
 * MorseChat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * MorseChat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MorseChat. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace pjz9n\morsechat;

use Closure;
use InvalidArgumentException;
use pjz9n\morsechat\command\MorseCommand;
use pjz9n\morsechat\task\GooKatakanaTask;
use pjz9n\morsechat\task\MorseSendTask;
use pjz9n\resourcepacktools\ResourcePack;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Main extends PluginBase implements Listener
{
    public const TYPE_SHORT = 0;
    public const TYPE_LONG = 1;

    /**
     * 0 => .
     * 1 => -
     */
    public const CHAR_MORSE_MAP = [
        "0" => "11111",
        "1" => "01111",
        "2" => "00111",
        "3" => "00011",
        "4" => "00001",
        "5" => "00000",
        "6" => "10000",
        "7" => "11000",
        "8" => "11100",
        "9" => "11110",
        "A" => "01",
        "B" => "1000",
        "C" => "1010",
        "D" => "100",
        "E" => "0",
        "F" => "0010",
        "G" => "110",
        "H" => "0000",
        "I" => "00",
        "J" => "0111",
        "K" => "101",
        "L" => "0100",
        "M" => "11",
        "N" => "10",
        "O" => "111",
        "P" => "0110",
        "Q" => "1101",
        "R" => "010",
        "S" => "000",
        "T" => "1",
        "U" => "001",
        "W" => "011",
        "V" => "0001",
        "X" => "1001",
        "Y" => "1011",
        "Z" => "1100",
        "ア" => "11011",
        "イ" => "01",
        "ウ" => "001",
        "エ" => "10111",
        "オ" => "01000",
        "カ" => "0100",
        "ガ" => "0100",
        "キ" => "10100",
        "ギ" => "1010000",
        "ク" => "0001",
        "グ" => "000100",
        "ケ" => "1011",
        "ゲ" => "101100",
        "コ" => "1111",
        "ゴ" => "111100",
        "サ" => "10101",
        "ザ" => "1010100",
        "シ" => "11010",
        "ジ" => "1101000",
        "ス" => "11101",
        "ズ" => "1110100",
        "セ" => "01110",
        "ゼ" => "0111000",
        "ソ" => "1110",
        "ゾ" => "111000",
        "タ" => "10",
        "ダ" => "1000",
        "チ" => "0010",
        "ヂ" => "001000",
        "ツ" => "0110",
        "ヅ" => "011000",
        "テ" => "00100",
        "デ" => "0010000",
        "ト" => "010",
        "ド" => "01000",
        "ナ" => "1010",
        "ニ" => "0000",
        "ヌ" => "1101",
        "ネ" => "0011",
        "ノ" => "1000",
        "ハ" => "11001",
        "パ" => "1100100110",
        "ヒ" => "1100",
        "ピ" => "110000110",
        "フ" => "0",
        "プ" => "000110",
        "ヘ" => "100",
        "ペ" => "10000110",
        "ホ" => "100",
        "ポ" => "10000110",
        "マ" => "1001",
        "ミ" => "00101",
        "ム" => "1",
        "メ" => "10001",
        "モ" => "10010",
        "ヤ" => "011",
        "ャ" => "011",
        "ヰ" => "01001",
        "ユ" => "10011",
        "ュ" => "10011",
        "ヱ" => "01100",
        "ヨ" => "11",
        "ョ" => "11",
        "ラ" => "000",
        "リ" => "110",
        "ル" => "10110",
        "レ" => "111",
        "ロ" => "0101",
        "ワ" => "101",
        "ヲ" => "0111",
        "ン" => "01010",
        "ー" => "01101",
        "。" => "00110",
        "〃" => "00",
        "(" => "101101",
        ")" => "010010",
    ];

    private static $caFilePath;

    public static function getCaFilePath(): string
    {
        return self::$caFilePath;
    }

    public function onEnable(): void
    {
        self::$caFilePath = $this->getDataFolder() . "cacert.pem";
        $this->saveDefaultConfig();
        $this->saveResource("cacert.pem");
        $this->saveResource("music.zip");
        ResourcePack::register($this->getDataFolder() . "music.zip");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register($this->getName(), new MorseCommand($this));
    }

    public function onchat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $this->toKatakana($event->getMessage(), function (string $converted): void {
            $this->getScheduler()->scheduleRepeatingTask(new MorseSendTask($this->textToMoles($converted)), 5);
        }, function () use ($player) {
            $player->sendMessage("えらー...");
        });
    }

    public function toKatakana(string $text, Closure $onComplete, Closure $onError): void
    {
        $this->getServer()->getAsyncPool()->submitTask(new GooKatakanaTask(
            $this->getConfig()->get("goo-app-id"),
            $text,
            function ($responseCode, $result) use ($onComplete, $onError): void {
                if ($responseCode === 200) {
                    $onComplete(json_decode($result, true)["converted"]);
                } else {
                    $onError();
                }
            }
        ));
    }

    /**
     * @return int[][]
     */
    public function textToMoles(string $text): array
    {
        $morse = [];
        foreach ($this->mbStrSplit($text) as $char) {
            $morse[] = $this->charToMorse($char);
        }
        return $morse;
    }

    /**
     * @return int[]|null
     */
    public function charToMorse(string $char): ?array
    {
        if (!array_key_exists($char, self::CHAR_MORSE_MAP)) {
            return null;
        }
        return array_map(function (string $string): int {
            return (int)$string;
        }, str_split(self::CHAR_MORSE_MAP[$char]));
    }

    /**
     * @return string[]
     */
    public function mbStrSplit(string $string, int $splitLength = 1): array
    {
        if ($splitLength <= 0) {
            $splitLength = 1;
        }
        $length = mb_strlen($string);
        $return = [];
        for ($i = 0; $i < $length; $i += $splitLength) {
            $return[] = mb_substr($string, $i, $splitLength);
        }
        return $return;
    }

    /**
     * @param Player[] $players
     */
    public static function sendMorse(int $type, array $players = []): void
    {
        if ($players === []) {
            $players = Server::getInstance()->getOnlinePlayers();
        }
        switch ($type) {
            case self::TYPE_SHORT:
                $soundName = "morsechat.short";
                break;
            case self::TYPE_LONG:
                $soundName = "morsechat.long";
                break;
            default:
                throw new InvalidArgumentException("Invalid type: $type");
        }
        foreach ($players as $player) {
            $packet = new PlaySoundPacket();
            $packet->soundName = $soundName;
            $packet->volume = 1.0;
            $packet->pitch = 1.0;
            $packet->x = $player->getX();
            $packet->y = $player->getY();
            $packet->z = $player->getZ();
            $player->dataPacket($packet);
        }
    }
}

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

namespace pjz9n\morsechat\task;

use Closure;
use pjz9n\morsechat\Main;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class GooKatakanaTask extends AsyncTask
{
    /** @var string */
    private $appId;

    /** @var string */
    private $text;

    /** @var string */
    private $caFilePath;

    public function __construct(string $appId, string $text, Closure $onComplete)
    {
        $this->appId = $appId;
        $this->text = $text;
        $this->caFilePath = Main::getCaFilePath();
        $this->storeLocal([$onComplete]);
    }

    public function onRun()
    {
        $curl = curl_init("https://labs.goo.ne.jp/api/hiragana");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            "app_id" => $this->appId,
            "sentence" => $this->text,
            "output_type" => "katakana"
        ]));
        curl_setopt($curl, CURLOPT_CAINFO, $this->caFilePath);
        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $this->setResult([$responseCode, $result]);
    }

    public function onCompletion(Server $server)
    {
        [$responseCode, $result] = $this->getResult();
        [$onComplete] = $this->fetchLocal();
        $onComplete($responseCode, $result);
    }
}

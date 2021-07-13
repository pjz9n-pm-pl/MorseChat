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

namespace pjz9n\morsechat\form;

use pjz9n\morsechat\Main;
use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class MorseListForm implements Form
{
    public function handleResponse(Player $player, $data): void
    {
    }

    public function jsonSerialize(): array
    {

        $rows = [];
        foreach (Main::CHAR_MORSE_MAP as $char => $morse) {
            $rows[] = $char . ": " . str_replace(["0", "1"], [".", "-"], $morse);
        }
        return [
            "type" => "form",
            "title" => "モールス信号表",
            "content" => implode(TextFormat::EOL, $rows),
            "buttons" => []
        ];
    }
}

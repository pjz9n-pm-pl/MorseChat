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

namespace pjz9n\morsechat\command;

use pjz9n\morsechat\form\MorseListForm;
use pjz9n\morsechat\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class MorseCommand extends PluginCommand implements CommandExecutor
{
    public function __construct(Plugin $owner)
    {
        parent::__construct("morse", $owner);
        $this->setDescription("モールス信号一覧");
        $this->setPermission("morsechat.command.morse");
        $this->setExecutor($this);
    }


    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof Player) {
            $sender->sendForm(new MorseListForm());
            return true;
        } else {
            $rows = [];
            foreach (Main::CHAR_MORSE_MAP as $char => $morse) {
                $rows[] = $char . ": " . str_replace(["0", "1"], [".", "-"], $morse);
            }
            $sender->sendMessage(implode(TextFormat::EOL, $rows));
            return true;
        }
    }
}

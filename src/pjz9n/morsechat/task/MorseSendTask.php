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

namespace pjz9n\morsechat\task;

use pjz9n\morsechat\Main;
use pocketmine\scheduler\Task;

class MorseSendTask extends Task
{
    /**
     * @var int[][]
     * -1 => なし
     * 0 => .
     * 1 => -
     */
    private $realMoles = [];

    /** @var int */
    private $cursor = 0;

    /**
     * @param int[][] $moles
     */
    public function __construct(array $moles)
    {
        foreach ($moles as $morse) {
            if ($morse == null) {
                continue;
            }
            $this->realMoles = array_merge($this->realMoles, array_merge([-1], $morse));
        }
        $this->realMoles = array_values($this->realMoles);
    }

    public function onRun(int $currentTick): void
    {
        if (!array_key_exists($this->cursor, $this->realMoles)) {
            $this->getHandler()->cancel();
            return;
        }
        $morse = -1;
        switch ($this->realMoles[$this->cursor]) {
            case 0:
                $morse = Main::TYPE_SHORT;
                break;
            case 1:
                $morse = Main::TYPE_LONG;
                break;
        }
        if ($morse !== -1) {
            Main::sendMorse($morse);
        }
        $this->cursor++;
    }
}

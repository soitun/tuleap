<?php
/**
 * Copyright Enalean (c) 2013-Present. All rights reserved.
 *
 * Tuleap and Enalean names and logos are registered trademarks owned by
 * Enalean SAS. All other trademarks or names are properties of their respective
 * owners.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

use Tuleap\AgileDashboard\Milestone\Backlog\IBacklogItem;

class AgileDashboard_Milestone_Backlog_BacklogItemCollection implements
    AgileDashboard_Milestone_Backlog_IBacklogItemCollection
{
    /** @var IBacklogItem[] */
    private $rows = [];

    /** @var array<int, bool> */
    private $index = [];

    /** @var string */
    private $parent_item_name = '';

    private int $total_available_size = 0;

    public function getParentItemName(): string
    {
        return $this->parent_item_name;
    }

    public function setParentItemName(string $name): void
    {
        $this->parent_item_name = $name;
    }

    public function push(IBacklogItem $item): void
    {
        $this->rows[]             = $item;
        $this->index[$item->id()] = true;
    }

    public function containsId(int $id): bool
    {
        return isset($this->index[$id]);
    }

    public function current(): mixed
    {
        return current($this->rows);
    }

    public function key(): mixed
    {
        return key($this->rows);
    }

    public function next(): void
    {
        next($this->rows);
    }

    public function rewind(): void
    {
        reset($this->rows);
    }

    public function valid(): bool
    {
        return current($this->rows) !== false;
    }

    public function count(): int
    {
        return count($this->rows);
    }

    public function getTotalAvaialableSize(): int
    {
        return $this->total_available_size;
    }

    public function setTotalAvaialableSize(int $size): void
    {
        $this->total_available_size = $size;
    }

    /**
     * @return list<int>
     */
    public function getItemIds(): array
    {
        return array_keys($this->index);
    }
}

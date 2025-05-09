<?php
/*
 * Copyright (c) Enalean, 2025 - Present. All Rights Reserved.
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

declare(strict_types=1);

namespace Tuleap\Tracker\FormElement\FieldSpecificProperties;

use Tuleap\DB\DataAccessObject;

final class ComputedFieldSpecificPropertiesDAO extends DataAccessObject implements DuplicateSpecificProperties, DeleteSpecificProperties, SearchSpecificProperties, SaveSpecificFieldProperties
{
    public function duplicate(int $from_field_id, int $to_field_id): void
    {
        $sql = 'REPLACE INTO tracker_field_computed (field_id, target_field_name)
                SELECT ?, target_field_name FROM tracker_field_computed WHERE field_id = ?';

        $this->getDB()->run($sql, $to_field_id, $from_field_id);
    }

    public function deleteFieldProperties(int $field_id): void
    {
        $this->getDB()->delete('tracker_field_computed', ['field_id' => $field_id]);
    }

    /**
     * @return null | array{field_id: int, target_field_name: ?string, default_value: ?float}
     */
    public function searchByFieldId(int $field_id): ?array
    {
        $sql = 'SELECT *
                FROM tracker_field_computed
                WHERE field_id = ? ';

        return $this->getDB()->row($sql, $field_id);
    }

    public function saveSpecificProperties(int $field_id, array $row): void
    {
        $target_field_name = $row['target_field_name'] ?? '';
        $default_value     = $this->computeDefaultValue($row);

        $sql = 'REPLACE INTO tracker_field_computed (field_id, default_value, target_field_name)
            VALUES (?, ?, ?)';

        $this->getDB()->run($sql, $field_id, $default_value, $target_field_name);
    }

    private function computeDefaultValue(array $row): mixed
    {
        $default_value = $row['default_value'] ?? '';
        if ($default_value === '') {
            return null;
        }
        return $default_value;
    }
}

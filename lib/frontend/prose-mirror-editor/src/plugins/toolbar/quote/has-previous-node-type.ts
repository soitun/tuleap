/*
 *  Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
 *
 *  Tuleap is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  Tuleap is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

import type { EditorState } from "prosemirror-state";
import type { NodeType } from "prosemirror-model";

export function hasPreviousNodeType(state: EditorState, node_type: NodeType): boolean {
    const { $from } = state.selection;

    for (let i = $from.depth; i >= 0; i--) {
        const node = $from.node(i);
        if (node.type === node_type) {
            return true;
        }
    }

    return false;
}

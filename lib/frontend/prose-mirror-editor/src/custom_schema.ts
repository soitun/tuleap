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

import { Schema } from "prosemirror-model";
import type { DOMOutputSpec, MarkSpec, NodeSpec } from "prosemirror-model";
import { addListNodes } from "prosemirror-schema-list";
import { schema } from "prosemirror-schema-basic";
import { createAsyncCrossReference } from "./plugins/cross-references/element/AsyncCrossReference";

const subscript_mark_spec: MarkSpec = {
    parseDOM: [{ tag: "sub" }],
    toDOM(): DOMOutputSpec {
        return ["sub", 0];
    },
};

const superscript_mark_spec: MarkSpec = {
    parseDOM: [{ tag: "sup" }],
    toDOM(): DOMOutputSpec {
        return ["sup", 0];
    },
};

const async_cross_reference_mark_spec: MarkSpec = {
    inclusive: false,
    spanning: false,
    attrs: {
        text: { validate: "string" },
        project_id: { validate: "number" },
    },
    toDOM(node): DOMOutputSpec {
        const { text, project_id } = node.attrs;
        return createAsyncCrossReference(text, project_id);
    },
};

export type EditorNodes = Record<string, NodeSpec>;
export const prosemirror_nodes = addListNodes(
    schema.spec.nodes,
    "(paragraph | code_block | heading) block*",
    "block",
).toObject();

export const buildCustomSchema = (editor_nodes: EditorNodes = {}): Schema => {
    return new Schema({
        nodes: Object.keys(editor_nodes).length > 0 ? editor_nodes : prosemirror_nodes,
        marks: {
            ...schema.spec.marks.toObject(),
            subscript: subscript_mark_spec,
            superscript: superscript_mark_spec,
            async_cross_reference: async_cross_reference_mark_spec,
        },
    });
};

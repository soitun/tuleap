<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('HTML_Element.php');

/**
 * Define an html textarea
 */
class HTML_Element_Textarea extends HTML_Element
{
    protected function renderValue()
    {
        $hp    = Codendi_HTMLPurifier::instance();
        $html  = '<textarea  id="' . $this->id . '" cols="40" rows="5" name="' . $hp->purify($this->name, CODENDI_PURIFIER_CONVERT_HTML) . '">';
        $html .=  $hp->purify($this->value, CODENDI_PURIFIER_CONVERT_HTML);
        $html .= '</textarea>';
        return $html;
    }
}

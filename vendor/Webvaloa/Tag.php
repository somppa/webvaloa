<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.im>
 *
 * All Rights Reserved.
 *
 * Contributor(s):
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
namespace Webvaloa;

use Libvaloa\Db;

/**
 * Handles Webvaloa tags.
 */
class Tag
{
    public function __construct()
    {
    }

    public function __set($k, $v)
    {
    }

    public function __get($k)
    {
    }

    public function byID($id)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT tag.*
            FROM tag
            WHERE tag.id = ?
            LIMIT 1';

        $stmt = $db->prepare($query);
        $stmt->set($id);

        try {
            $stmt->execute();

            $row = $stmt->fetch();
            if (isset($row->id)) {
                return $row;
            }
        } catch (Exception $e) {
        }

        return false;
    }

    public function delete($id)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            DELETE
            FROM tag
            WHERE tag.id = ?';

        $stmt = $db->prepare($query);
        $stmt->set((int) $id);

        try {
            $stmt->execute();
        } catch (Exception $e) {
        }
    }

    public function findTagByName($name, $parent_id = null)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        if ($parent_id !== null) {
            $q = 'AND tag.parent_id = ?';
        } else {
            $q = 'AND tag.parent_id IS NULL';
        }

        $query = '
            SELECT tag.*
            FROM tag
            WHERE tag.tag = ?
            '.$q.'
            LIMIT 1';

        $stmt = $db->prepare($query);
        $stmt->set($name);

        if ($parent_id !== null) {
            $stmt->set($parent_id);
        }

        try {
            $stmt->execute();

            $row = $stmt->fetch();
            if (isset($row->id)) {
                return $row;
            }
        } catch (Exception $e) {
        }

        return false;
    }

    public function addTag($tag, $parent_id = null)
    {
        if ($this->findTagByName($tag, $parent_id)) {
            throw new RuntimeException('Tag already exists');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $object = new Db\Object('tag', $db);
        $object->tag = $tag;
        $object->parent_id = $parent_id;

        return $object->save();
    }

    public function tags($parent_id = false)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT tag.*
            FROM tag';

        $byParent = false;
        if (is_numeric($parent_id) ||  is_array($parent_id)) {
            if (!is_array($parent_id)) {
                $tags = (array) $parent_id;
            }

            foreach ($tags as $k => $v) {
                if (!is_numeric($v)) {
                    throw new UnexpectedValueException('Malformed tag id');
                }
            }

            $query .= ' WHERE parent_id IN('.implode(',', $parent_id).')';

            $byParent = true;
        }

        $stmt = $db->prepare($query);

        try {
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
        }

        return false;
    }
}

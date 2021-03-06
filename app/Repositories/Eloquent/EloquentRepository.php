<?php
/*
 * Pterodactyl - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Pterodactyl\Repositories\Eloquent;

use Webmozart\Assert\Assert;
use Pterodactyl\Repository\Repository;
use Illuminate\Database\Query\Expression;
use Pterodactyl\Contracts\Repository\RepositoryInterface;
use Pterodactyl\Exceptions\Model\DataValidationException;
use Pterodactyl\Exceptions\Repository\RecordNotFoundException;
use Pterodactyl\Contracts\Repository\Attributes\SearchableInterface;

abstract class EloquentRepository extends Repository implements RepositoryInterface
{
    /**
     * {@inheritdoc}
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getBuilder()
    {
        return $this->getModel()->newQuery();
    }

    /**
     * {@inheritdoc}
     * @param bool $force
     * @return \Illuminate\Database\Eloquent\Model|bool
     */
    public function create(array $fields, $validate = true, $force = false)
    {
        Assert::boolean($validate, 'Second argument passed to create must be boolean, recieved %s.');
        Assert::boolean($force, 'Third argument passed to create must be boolean, received %s.');

        $instance = $this->getBuilder()->newModelInstance();

        if ($force) {
            $instance->forceFill($fields);
        } else {
            $instance->fill($fields);
        }

        if (! $validate) {
            $saved = $instance->skipValidation()->save();
        } else {
            if (! $saved = $instance->save()) {
                throw new DataValidationException($instance->getValidator());
            }
        }

        return ($this->withFresh) ? $instance->fresh() : $saved;
    }

    /**
     * {@inheritdoc}
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function find($id)
    {
        Assert::numeric($id, 'First argument passed to find must be numeric, received %s.');

        $instance = $this->getBuilder()->find($id, $this->getColumns());

        if (! $instance) {
            throw new RecordNotFoundException();
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findWhere(array $fields)
    {
        return $this->getBuilder()->where($fields)->get($this->getColumns());
    }

    /**
     * {@inheritdoc}
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findFirstWhere(array $fields)
    {
        $instance = $this->getBuilder()->where($fields)->first($this->getColumns());

        if (! $instance) {
            throw new RecordNotFoundException;
        }

        return $instance;
    }

    /**
     * {@inheritdoc}.
     */
    public function findCountWhere(array $fields)
    {
        return $this->getBuilder()->where($fields)->count($this->getColumns());
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id, $destroy = false)
    {
        Assert::numeric($id, 'First argument passed to delete must be numeric, received %s.');
        Assert::boolean($destroy, 'Second argument passed to delete must be boolean, received %s.');

        $instance = $this->getBuilder()->where($this->getModel()->getKeyName(), $id);

        return ($destroy) ? $instance->forceDelete() : $instance->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteWhere(array $attributes, $force = false)
    {
        Assert::boolean($force, 'Second argument passed to deleteWhere must be boolean, received %s.');

        $instance = $this->getBuilder()->where($attributes);

        return ($force) ? $instance->forceDelete() : $instance->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $fields, $validate = true, $force = false)
    {
        Assert::numeric($id, 'First argument passed to update must be numeric, received %s.');
        Assert::boolean($validate, 'Third argument passed to update must be boolean, received %s.');
        Assert::boolean($force, 'Fourth argument passed to update must be boolean, received %s.');

        $instance = $this->getBuilder()->where('id', $id)->first();

        if (! $instance) {
            throw new RecordNotFoundException();
        }

        if ($force) {
            $instance->forceFill($fields);
        } else {
            $instance->fill($fields);
        }

        if (! $validate) {
            $saved = $instance->skipValidation()->save();
        } else {
            if (! $saved = $instance->save()) {
                throw new DataValidationException($instance->getValidator());
            }
        }

        return ($this->withFresh) ? $instance->fresh() : $saved;
    }

    /**
     * {@inheritdoc}
     */
    public function updateWhereIn($column, array $values, array $fields)
    {
        Assert::stringNotEmpty($column, 'First argument passed to updateWhereIn must be a non-empty string, received %s.');

        return $this->getBuilder()->whereIn($column, $values)->update($fields);
    }

    /**
     * {@inheritdoc}
     */
    public function massUpdate(array $where, array $fields)
    {
        // TODO: Implement massUpdate() method.
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $instance = $this->getBuilder();
        if (interface_exists(SearchableInterface::class)) {
            $instance = $instance->search($this->searchTerm);
        }

        return $instance->get($this->getColumns());
    }

    /**
     * {@inheritdoc}
     */
    public function insert(array $data)
    {
        return $this->getBuilder()->insert($data);
    }

    /**
     * Insert multiple records into the database and ignore duplicates.
     *
     * @param array $values
     * @return bool
     */
    public function insertIgnore(array $values)
    {
        if (empty($values)) {
            return true;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }

        $bindings = array_values(array_filter(array_flatten($values, 1), function ($binding) {
            return ! $binding instanceof Expression;
        }));

        $grammar = $this->getBuilder()->toBase()->getGrammar();
        $table = $grammar->wrapTable($this->getModel()->getTable());
        $columns = $grammar->columnize(array_keys(reset($values)));

        $parameters = collect($values)->map(function ($record) use ($grammar) {
            return sprintf('(%s)', $grammar->parameterize($record));
        })->implode(', ');

        $statement = "insert ignore into $table ($columns) values $parameters";

        return $this->getBuilder()->getConnection()->statement($statement, $bindings);
    }

    /**
     * {@inheritdoc}
     * @return bool|\Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $where, array $fields, $validate = true, $force = false)
    {
        Assert::boolean($validate, 'Third argument passed to updateOrCreate must be boolean, received %s.');
        Assert::boolean($force, 'Fourth argument passed to updateOrCreate must be boolean, received %s.');

        $instance = $this->withColumns('id')->findWhere($where)->first();

        if (! $instance) {
            return $this->create(array_merge($where, $fields), $validate, $force);
        }

        return $this->update($instance->id, $fields, $validate, $force);
    }
}

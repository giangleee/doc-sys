<?php

namespace App\Repositories;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Exceptions\BadRequestException;

abstract class BaseRepository
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    protected $msgNotFound = '存在しません。';

    protected $perPage = 20;

    /**
     * EloquentRepository constructor.
     */
    public function __construct()
    {
        $this->setModel();
    }

    /**
     * get model
     * @return string
     */
    abstract public function getModel();

    /**
     * Set model
     */
    public function setModel()
    {
        $this->model = app()->make(
            $this->getModel()
        );
    }

    public function getObject($id)
    {
        try {
            $model = $this->getModel();
            if ($id instanceof $model) {
                $result = $id;
            } else {
                $result = $this->findOrFail($id);
            }
            return $result;
        } catch (ModelNotFoundException $e) {
            return null;
        }
    }

    /**
     * Get All
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll()
    {
        return $this->model->get();
    }

    /**
     * Get one
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        $result = $this->model->find($id);

        return $result;
    }

    public function getMany($ids)
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    public function filter($where)
    {
        return $this->model->where($where)->get();
    }

    public function filterFirst($where)
    {
        return $this->model->where($where)->first();
    }

    /**
     * Check if model exists
     * @param $id
     * @return mixed
     */
    public function exists($id)
    {
        return $this->model->exists($id);
    }

    /**
     * Get one
     * @param $id
     * @return mixed
     */
    public function findOrFail($id)
    {
        try {
            $result = $this->model->findOrFail($id);
        } catch (\Exception $e) {
            throw new ModelNotFoundException($this->msgNotFound, 0);
        }

        return $result;
    }

    public function findOrFailByCompositeKey($id, array $relationshipKeys)
    {
        $model = $this->model->where('id', $id)
            ->where($relationshipKeys)
            ->first();
        if (!$model) {
            throw new ModelNotFoundException($this->msgNotFound, 0);
        }
        return $model;
    }

    /**
     * Get one
     * @param $id
     * @return mixed
     */
    public function findOrBad($id)
    {
        try {
            $result = $this->model->findOrFail($id);
        } catch (\Exception $e) {
            throw new BadRequestException($this->msgNotFound, 0);
        }

        return $result;
    }

    /**
     * Get first
     * @return mixed
     */

    public function first()
    {
        return $this->model->first();
    }

    /**
     * Create
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    /**
     * Update
     * @param $id
     * @param array $attributes
     * @return bool|mixed
     */
    public function update($id, array $attributes)
    {
        $object = $this->getObject($id);
        if ($object) {
            $object->update($attributes);
            return $object;
        }
        return false;
    }

    public function bulkUpdate(array $values, array $attributes, $key = 'id')
    {
        if (!empty($attributes)) {
            return $this->model->whereIn($key, $values)
                ->update($attributes);
        }
        return false;
    }

    /**
     * Delete
     */
    public function delete($id)
    {
        $result = $this->getObject($id);
        if ($result) {
            $result->delete();
            return true;
        }

        return false;
    }

    public function insertMany($params)
    {
        return DB::table($this->model->getTable())->insert($params);
    }

    public function optimisticLockForUpdate($id, array $attributes, Closure $constrain = null)
    {
        do {
            $record = $this->getObject($id);
            if ($constrain) {
                $constrain($record, $attributes);
            }
            $updated = $this->model->where('id', $record->id)
                ->where('updated_at', '=', $record->updated_at)
                ->update($attributes);
        } while (!$updated);
    }

    public function updateOrCreateData($condition, $values)
    {
        return $this->model->updateOrCreate($condition, $values);
    }

    public function rollbackAndUpdate($id, array $attributes)
    {
        $object = $this->model->withTrashed()->find($id);
        if ($object) {
            $object->restore();
            $object->update($attributes);
            return $object;
        }
        return false;
    }

    public function firstOrCreateData($condition, $values)
    {
        return $this->model->firstOrCreate($condition, $values);
    }

    public function deleteByField($field, $values)
    {
        return $this->model->whereIn($field, $values)->delete();
    }

    public function bulkDelete(array $values, $key = 'id')
    {
        return $this->model->whereIn($key, $values)->delete();
    }
}

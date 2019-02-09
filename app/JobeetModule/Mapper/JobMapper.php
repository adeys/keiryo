<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09/02/2019
 * Time: 17:48
 */

namespace App\JobeetModule\Mapper;


use App\JobeetModule\Entity\Job;
use Simplex\DataMapper\Mapping\EntityMapper;

class JobMapper extends EntityMapper
{

    /**
     * @var string
     */
    protected $table = 'jobs';

    /**
     * Creates an entity from given input values
     *
     * @param array $input
     * @return object
     */
    public function createEntity(array $input): object
    {
        $job = new Job($input['company'], $input['position'], $input['location']);

        if (isset($input['category_id'])) {
            $job->setCategory($input['category_id']);
        }

        if (isset($input['is_public'])) {
            $job->setPublic($input['is_public']);
        }

        if (isset($input['type'])) {
            $job->setType(Job::TYPES[$input['type']]);
        }

        if (isset($input['created_at'])) {
            $job->setCreatedAt(\DateTime::createFromFormat('Y-m-d H:i:s', $input['created_at']));
        }

        if (isset($input['expires_at'])) {
            $job->setExpiresAt(\DateTime::createFromFormat('Y-m-d H:i:s', $input['expires_at']));
        }

        foreach (['id', 'category', 'logo', 'url', 'application', 'description', 'public', 'token', 'email'] as $field) {
            if (isset($input[$field])) {
                $method = 'set' . ucfirst($field);
                $job->{$method}($input[$field]);
            }
        }

        $this->uow->getIdentityMap()->add($job, $job->getId());
        return $job;
    }

    /**
     * @param $id
     * @return object
     */
    public function find($id): object
    {
        return $this->query('j')
            ->addSelect(['j.id', 'company', 'location', 'position', 'description', 'application', 'type'])
            ->addSelect('c.name', 'category')
            ->where('j.id', $id)
            ->innerJoin(['categories', 'c'], 'j.category_id', 'c.id')
            ->first();
    }

    /**
     * Extract an entity to persistable state
     *
     * @param object $entity
     * @return array
     */
    public function extract(object $entity): array
    {
        return [

        ];
    }

    /**
     * Performs an entity update
     *
     * @param Job $job
     * @return mixed
     */
    public function update(object $job)
    {
        $changes = $this->uow->getChangeSet($job);
        $changes = $this->map($changes);

        return $this->query()
            ->where('id', $job->getId())
            ->update($changes);
    }

    /**
     * Map props to field
     *
     * @param array $input
     * @return array
     */
    protected function map(array $input): array
    {
        $map = ['category' => 'category_id', 'public' => 'is_public'];
        foreach ($map as $key => $value) {
            if (isset($input[$key])) {
                $input[$map[$key]] = $input[$key];
                unset($input[$key]);
            }
        }

        if (isset($input['type'])) {
            $input['type'] = array_flip(Job::TYPES)[$input['type']];
        }

        if (isset($input['expiresAt'])) {
            $input['expires_at'] = $input['expiresAt']->format('Y-m-d H:i:s');
            unset($input['expiresAt']);
        }

        return $input;
    }

    /**
     * Performs an entity deletion
     *
     * @param object $entity
     * @return mixed
     */
    public function delete(object $entity)
    {
        // TODO: Implement delete() method.
    }
}
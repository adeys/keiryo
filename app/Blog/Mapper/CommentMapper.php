<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09/02/2019
 * Time: 15:59
 */

namespace App\Blog\Mapper;


use App\Blog\Entity\Comment;
use Simplex\Database\DatabaseInterface;
use Simplex\DataMapper\Mapping\EntityMapper;
use Simplex\DataMapper\UnitOfWork;

class CommentMapper extends EntityMapper
{

    /**
     * CommentMapper constructor.
     * @param DatabaseInterface $database
     * @param UnitOfWork $uow
     */
    public function __construct(DatabaseInterface $database, UnitOfWork $uow)
    {
        parent::__construct('comments', $database, $uow);
    }

    /**
     * Creates an entity from given input values
     *
     * @param array $input
     * @return object
     */
    public function createEntity(array $input): object
    {
        $comment = new Comment();
        $comment->setContent($input['content']);
        $comment->setAuthor($input['usr_pseudo']);
        $comment->setCreatedAt(\DateTime::createFromFormat('Y-m-d H:i:s', $input['created_at']));

        if (isset($input['post_id'])) {
            $comment->setPostId($input['post_id']);
        }

        if (isset($input['usr_email'])) {
            $comment->setEmail($input['usr_email']);
        }

        $this->uow->getIdentityMap()->add($comment);

        return $comment;
    }

    /**
     * Performs an entity update
     *
     * @param object $entity
     * @return mixed
     */
    public function update(object $entity)
    {
        // TODO: Implement update() method.
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

    /**
     * Extract an entity to persistable state
     *
     * @param Comment $entity
     * @return array
     */
    public function extract(object $entity): array
    {
        return [
            'content' => $entity->getContent(),
            'post_id' => $entity->getPostId(),
            'created_at' => $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            'usr_pseudo' => $entity->getAuthor(),
            'usr_email' => $entity->getEmail()
        ];
    }
}
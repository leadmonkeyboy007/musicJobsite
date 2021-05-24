<?php

namespace Vocalizr\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vocalizr\AppBundle\Repository\GenreRepository")
 * @ORM\Table(name="genre")
 */
class Genre
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $title;

    /**
     * Relationships
     */

    /**
     * @ORM\ManyToMany(targetEntity="Project", mappedBy="genres")
     */
    protected $projects;

    /**
     * @ORM\ManyToMany(targetEntity="UserInfo", mappedBy="genres")
     */
    protected $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user_genres = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Genre
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Genre
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add projects
     *
     * @param \Vocalizr\AppBundle\Entity\Project $projects
     *
     * @return Genre
     */
    public function addProject(\Vocalizr\AppBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;

        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Vocalizr\AppBundle\Entity\Project $projects
     */
    public function removeProject(\Vocalizr\AppBundle\Entity\Project $projects)
    {
        $this->projects->removeElement($projects);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Return the string representation of the Genre entity
     *
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }

    /**
     * Add users
     *
     * @param \Vocalizr\AppBundle\Entity\UserInfo $users
     *
     * @return Genre
     */
    public function addUser(\Vocalizr\AppBundle\Entity\UserInfo $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Vocalizr\AppBundle\Entity\UserInfo $users
     */
    public function removeUser(\Vocalizr\AppBundle\Entity\UserInfo $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
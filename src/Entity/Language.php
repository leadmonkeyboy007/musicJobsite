<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LanguageRepository")
 * @ORM\Table(name="language")
 */
class Language
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity="Project"), mappedBy="languages")
     */
    protected $projects;

    /**
     * @ORM\OneToMany(targetEntity="UserInfoLanguage", mappedBy="language")
     */
    protected $userLanguages;

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
     * @return Language
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
     * Add projects
     *
     * @param \App\Entity\Project $projects
     *
     * @return Genre
     */
    public function addProject(\App\Entity\Project $projects)
    {
        $this->projects[] = $projects;

        return $this;
    }

    /**
     * Remove projects
     *
     * @param \App\Entity\Project $projects
     */
    public function removeProject(\App\Entity\Project $projects)
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
     * Constructor
     */
    public function __construct()
    {
        $this->projects = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users    = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param UserInfoLanguage $userLanguage
     *
     * @return Language
     */
    public function addUserLanguage(UserInfoLanguage $userLanguage)
    {
        $this->userLanguages[] = $userLanguage;

        return $this;
    }

    /**
     * @param UserInfoLanguage $userLanguage
     */
    public function removeUserLanguage(UserInfoLanguage $userLanguage)
    {
        $this->userLanguages->removeElement($userLanguage);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserLanguages()
    {
        return $this->userLanguages;
    }
}
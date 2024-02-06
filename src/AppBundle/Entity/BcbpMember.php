<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * BcbpMember
 *
 * @ORM\Table(name="tbl_bcbp_members")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BcbpMemberRepository")
 * @UniqueEntity(fields={"name"},message="This name already exists.", errorPath="name")
 */
class BcbpMember
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

     /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="unit_no", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $unitNo;

     /**
     * @var string
     *
     * @ORM\Column(name="ag_no", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $agNo;
    
     /**
     * @var int
     *
     * @ORM\Column(name="t_units", type="integer")
     * @Assert\NotBlank()
     */
    private $tUnits;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt; 

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=255)
     */
    private $remarks;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     */
    private $status;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return BcbpMember
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return BcbpMember
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set unitNo
     *
     * @param string $unitNo
     *
     * @return BcbpMember
     */
    public function setUnitNo($unitNo)
    {
        $this->unitNo = $unitNo;

        return $this;
    }

    /**
     * Get unitNo
     *
     * @return string
     */
    public function getUnitNo()
    {
        return $this->unitNo;
    }

    /**
     * Set agNo
     *
     * @param string $agNo
     *
     * @return BcbpMember
     */
    public function setAgNo($agNo)
    {
        $this->agNo = $agNo;

        return $this;
    }

    /**
     * Get agNo
     *
     * @return string
     */
    public function getAgNo()
    {
        return $this->agNo;
    }

    /**
     * Set tUnits
     *
     * @param integer $tUnits
     *
     * @return BcbpMember
     */
    public function setTUnits($tUnits)
    {
        $this->tUnits = $tUnits;

        return $this;
    }

    /**
     * Get tUnits
     *
     * @return integer
     */
    public function getTUnits()
    {
        return $this->tUnits;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return BcbpMember
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return BcbpMember
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;

        return $this;
    }

    /**
     * Get remarks
     *
     * @return string
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return BcbpMember
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}

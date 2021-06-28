<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordUpdate
{

    private string $oldPassword;

    /**
     * @Assert\Length(
     *  min=8,
     *  max=25,
     *  minMessage="Le nouveau mot de passe doit faire au moins {{ limit }} caractères",
     *  minMessage="Le nouveau mot de passe ne doit pas faire au moins {{ limit }} caractères"
     * )
     */
    private string $newPassword;

    /**
     * @Assert\IdenticalTo(
     *  propertyPath="newPassword",
     *  message="Veuillez confirmer correctement le nouveau mot de passe"
     * )
     */ 
    private string $confirmNewPassword;

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getConfirmNewPassword(): ?string
    {
        return $this->confirmNewPassword;
    }

    public function setConfirmNewPassword(string $confirmNewPassword): self
    {
        $this->confirmNewPassword = $confirmNewPassword;

        return $this;
    }
}

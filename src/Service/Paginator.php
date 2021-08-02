<?php

namespace App\Service;

use Exception;
use App\Entity\User;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Paginator class.
 * 
 * Sert à extraire toute la notion de calcul et sert également à récupérer les données de façon paginées.
 * 
 * Cette classe est utilisable dans le cas où on est dans la page qui contient toutes les items et vous souhaitez de les afficher de façon paginées, ou dans le cas où on est dans une page privée comme par exemple la page de profil.
 * 
 * Elle nécessite après l'avoir injécté de passer le nom de l'entité et la page courante
 * 
 * @author Youssef Ait Bihi <youssefaitbihi@gmail.com>
 */
class Paginator
{  
    
    /**
     * Le gestionnaire de l'entité qui permet de trouver le dépôt (Repository) adéquat dont on a besoin
     * 
     * @var EntityManagerInterface $entityManagerInterface
     */
    private EntityManagerInterface $entityManagerInterface;

    /**
     * Le nom de l'entité
     * 
     * @var string $entityClassName
     */
    private string $entityClassName;

    /**
     * Le moteur de template twig qui permet de générer facilement le rendu de la pagination
     * 
     * @var Environment $twig
     */
    private Environment $twig;

    /**
     * Le service RequestStack qui permet récupérer notamment la route courante
     * 
     * @var RequestStack $requestStack 
     */
    private RequestStack $requestStack;

    /**
     * Le propriétaire des annonces qui permet de connaitre si on est sur la page de profil
     * 
     * @var User $owner
     */
    private ?User $owner;
    
    /**
     * Les items (objets) paginés et en ordre
     * 
     * @var array $items
     */
    private array $items = [];

    /**
     * Le max items (objets) à récupérer
     * 
     * @var int $limit
     */
    private int $limit = 8;

    /**
     * La page courante sur laquelle on se situe actuellement
     * 
     * @var int $currentPage
     */
    private int $currentPage;

    /**
     * L'ordre des items (objects). Par défauts de façon descandante
     * 
     * @var string $orderBy
     */
    private string $orderBy = 'desc';

    /**
     * Le chemin de la template de la pagination
     * 
     * @var string $paginationTemplatePath
     */
    private string $paginationTemplatePath;

    /**
     * Le constructeur du service de la classe Paginator qui va injecter tous les services dont on a besoin
     * 
     * Se souvenir de configurer le fichier services.yaml en définissant l'argument paginationTemplatePath qui est le chemin de la template de la pagination
     *
     * @param EntityManagerInterface $entityManagerInterface
     * @param Environment $twig
     * @param RequestStack $requestStack
     * @param string $paginationTemplatePath
     */
    public function __construct(
        EntityManagerInterface $entityManagerInterface,
        Environment $twig,
        RequestStack $requestStack,
        string $paginationTemplatePath
    )
    {
        $this->entityManagerInterface = $entityManagerInterface;
        $this->twig = $twig;
        $this->requestStack = $requestStack;
        $this->paginationTemplatePath = $paginationTemplatePath;
    }

    /**
     * Permet de récupérer le nom de l'entité sur laquelle l'entityManagerInterface compte sur lui pour récupérer le repository
     *
     * @return string
     */
    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * Permet de spécifier le nom de l'entité sur laquelle l'entityManagerInterface compte sur lui pour récupérer le repository
     *
     * @param string $entityClassName
     * 
     * @return self
     */
    public function setEntityClassName(string $entityClassName): self
    {
        $this->entityClassName = $entityClassName;

        return $this;
    }

    /**
     * Permet de récupérer le propriètaire dans le cas où on est dans la page de profil
     *
     * @return User
     */
    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * Permet de spécifier le propriètaire dans le cas où on est dans la page de profil
     *
     * @param User $owner
     * 
     * @return self
     */
    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Permet de récupérer le nombre maximale des items
     *
     * @return integer
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Permet de spécifier le nombre maximale des items
     *
     * @param integer $limit
     * 
     * @return self
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Permet de récupérer le page courante sur laquelle on se trouve
     *
     * @return integer
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Permet de spécifier la page courante sur laquelle on se trouve
     *
     * @param integer $currentPage
     * 
     * @return self
     */
    public function setCurrentPage(int $currentPage): self
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * Permet de récupérer l'ordre des items de façon descandante ou ascendante (déscandante par défaut)
     *
     * @return string
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * Permet de spécifier l'ordre des items de façon descandante ou ascendante (déscandante par défaut) 
     *
     * @param string $orderBy
     * 
     * @return self
     */
    public function setOrderBy(string $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * Permet de récupérer le repository adéquat
     * 
     * @throws Exception si la propriété entityClassName n'est pas définie
     *
     * @return ObjectRepository
     */
    private function getRepository(): ObjectRepository
    {
        if (empty($this->entityClassName)) {
            throw new Exception("Après avoir injécté ce service vous devez spécifier l'enitité sur laquelle le service va compter sur lui pour récupérer les items de façon paginées. Essayer d'utiliser la méthode setEntityClassName()");
        }

        return $this->entityManagerInterface->getRepository($this->getEntityClassName());
    }

    /**
     * Permet de calculer et de retourner le nombre de pages
     *
     * @return int
     */
    public function calculatePages(): int
    {
        $criteria = [];

        // Dans le cas où on est dans la page de profil
        if (!empty($this->owner)) {
            $criteria = array_merge(
                $criteria,
                ['owner' => $this->owner]
            );
        }

        $total = count($this->getRepository()->findBy($criteria));

        return ceil($total / $this->getLimit());
    }

    /**
     * Permet de calculer et de retourner l'offset
     * 
     * @throws Exception si la propriété currentPage n'est pas encore définie
     *
     * @return int
     */
    private function calculateOffset(): int
    {   
        if (empty($this->currentPage)) {
            throw new Exception("Après avoir injécté ce service, vous devez spécifié la page courante en utilisant la méthode setCurrentPage()");
        }
        
        // Le calcul de l'offset est basé sur la page courante sur laquelle on se trouve
        return ($this->getCurrentPage() - 1) * $this->getLimit();
    }

    /**
     * Permet de récupérer la route de la page actuelle sur laquelle on se trouve
     *
     * @return string
     */
    private function getRouteName(): string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        return $currentRequest->attributes->get('_route');
    }

    /**
     * Permet de renvoyer le rendu de la pagination au sein d'une template twig
     * 
     * Pour que la méthode render() fonctionne correctement, nous devons passer comme paramètre le nom de la template de la pagination, et on lui passant les paramètres nécessaires de la template
     * 
     * - paginator  ===> L'objet courant
     * - route      ===> Le nom de la route de la page actuelle
     * - user       ===> Si on est dans la page de profil
     *
     * @return void
     */
    public function render(): void
    {
        $context = [
            'paginator' => $this,
            'route'     => $this->getRouteName()
        ];

        // Dans le cas où on est dans la page de profil
        if (!empty($this->owner)) {
            $context = array_merge($context, [
                'user' => $this->owner
            ]);
        }

        $this->twig->display(
            $this->paginationTemplatePath,
            $context
        );
    }

    /**
     * Permet de récupérer les items (objets) paginés en ordre demandé
     *
     * @return array
     */
    public function fetchItems(): array
    {
        $criteria = [];

        // Dans le cas où on est dans la page de profil
        if (!empty($this->owner)) {
            $criteria = array_merge($criteria, [
                'owner' => $this->owner
            ]);
        }

        if (empty($this->items)) {
            $this->items = $this->getRepository()->findBy(
                $criteria,
                ['id' => $this->orderBy],
                $this->getLimit(),
                $this->calculateOffset()
            );
        }

        return $this->items; 
    } 
    
}

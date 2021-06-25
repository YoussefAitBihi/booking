<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Allow us to set the values of the form
 */
trait FormHelperType
{

    /** @var RequestStack $requestStack */
    private RequestStack $requestStack;

    /** @var array $routeName */
    private array $routeName = ['ad_edit'];

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Set values of the attributes of the form
     *
     * @param string $label
     * @param string $placeholder
     * @return array
     */
    public function setValuesOfAttributesForm(
        string $label, 
        string $placeholder,
        array $options = []
    ): array
    {
        return [
            'label'     => $label,
            'attr'      => array_merge([
                'placeholder' => $placeholder
            ], $options)
        ];

    }

    /**
     * Set values of the attributes of the file form
     *
     * @param string $fileMaxSize
     * @param array  $mimeTypes
     * @param string $fileMaxSizeError
     * @param string $mimeTypesError
     * @return array
     */
    public function setValuesOfAttributesFileForm(
        string $fileMaxSize, 
        array $mimeTypes,
        string $fileMaxSizeError,
        string $mimeTypesError
    ): array
    {

        $params = array(
            'label'     => false,
            'mapped'    => false,
            'constraints' => [
                new File([
                    'maxSize' => $fileMaxSize,
                    'mimeTypes' => $mimeTypes,
                    'maxSizeMessage' => $fileMaxSizeError,
                    'mimeTypesMessage' => $mimeTypesError
                ])
            ]
        );

        // If we're in the edit page
        if ($this->getRouteName() === "ad_edit") {
            $params = array_merge(
                array('required' => false), 
                $params
            );
        }

        return $params;
    }

    /**
     * Get the route name of the page
     *
     * @return string
     */
    private function getRouteName(): string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        return $currentRequest->attributes->get('_route'); 
    }

}

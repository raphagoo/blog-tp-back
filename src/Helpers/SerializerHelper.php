<?php


namespace App\Helpers;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerHelper
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param $toSerialize object|array
     * @param $groups string|array
     * @return Response
     */
    public function prepareResponse($toSerialize, $groups, $base64 = null): Response
    {
        $jsonContent = $this->serializer->serialize($toSerialize, 'json', [
            'circular_reference_handler' => function($object){
                return $object->getId();
            },
            'groups' => $groups
        ]);

        dd($jsonContent);

        $response = new Response($jsonContent);

        $response->headers->set('Content-type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    /**
     * @param $jsonObject
     * @param $objectClass
     * @param $objectToPopulate
     * @param null $groups
     * @return array|object
     */
    public function deserializeRequest($jsonObject, $objectClass, $objectToPopulate, $groups = null){
        return $this->serializer->deserialize($jsonObject, $objectClass, 'json', ['object_to_populate' => $objectToPopulate, 'deep_object_to_populate' => true, 'groups' => $groups]);
    }
}

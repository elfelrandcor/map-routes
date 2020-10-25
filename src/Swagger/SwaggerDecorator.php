<?php


namespace App\Swagger;


use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDecorator implements NormalizerInterface {

    private $decorated;

    public function __construct(NormalizerInterface $decorated) {
        $this->decorated = $decorated;
    }

    public function normalize($object, string $format = null, array $context = []) {

        $customDocumentation = $this->getCustomDocumentation();

        $docs = $this->decorated->normalize($object, $format, $context);

        return array_merge_recursive($customDocumentation, $docs);
    }

    public function supportsNormalization($data, string $format = null) {
        return $this->decorated->supportsNormalization($data, $format);
    }

    private function getCustomDocumentation() : array {
        return [
            'paths' => [
                '/api/authentication_token' => [
                    'post' => [
                        'tags'       => ['Authentication'],
                        'summary'    => 'Performs a login attempt, returning a valid token on success',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type'       => 'object',
                                        'required'   => [
                                            'email',
                                            'password'
                                        ],
                                        'properties' => [
                                            'email'    => [
                                                'type' => 'string'
                                            ],
                                            'password' => [
                                                'type' => 'string'
                                            ]
                                        ]
                                    ],
                                ],
                            ],
                        ],
                        'responses'  => [
                            200 => [
                                'description' => 'token'
                            ],
                            401 => [
                                'description' => 'Bad credentials'
                            ],
                            400 => [
                                'description' => 'Bad request'
                            ],
                        ],
                    ]
                ]
            ]
        ];
    }
}
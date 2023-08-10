<?php
/**
 * @author: Ivo Meißner
 * Date: 29.02.16
 * Time: 16:35
 */

namespace GraphQLRelay\tests\Node;


use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQLRelay\Node\Plural;

class PluralTest extends \PHPUnit_Framework_TestCase {
    protected static function getSchema()
    {
        $userType = new ObjectType([
            'name' => 'User',
            'fields' => fn() => [
                'username' => [
                    'type' => Type::string()
                ],
                'url' => [
                    'type' => Type::string()
                ]
            ]
        ]);

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => fn() => [
                'usernames' => Plural::pluralIdentifyingRootField([
                    'argName' => 'usernames',
                    'description' => 'Map from a username to the user',
                    'inputType' => Type::string(),
                    'outputType' => $userType,
                    'resolveSingleInput' => fn($userName, $context, $info) => [
                        'username' => $userName,
                        'url' => 'www.facebook.com/' . $userName . '?lang=' . $info->rootValue['lang']
                    ]
                ])
            ]
        ]);

        return new Schema([
            'query' => $queryType
        ]);
    }

    public function testAllowsFetching() {
        $query = '{
          usernames(usernames:["dschafer", "leebyron", "schrockn"]) {
            username
            url
          }
        }';

        $expected = ['usernames' =>
            [0 =>
                ['username' => 'dschafer', 'url' => 'www.facebook.com/dschafer?lang=en'], 1 =>
                ['username' => 'leebyron', 'url' => 'www.facebook.com/leebyron?lang=en'], 2 =>
                ['username' => 'schrockn', 'url' => 'www.facebook.com/schrockn?lang=en']]];

        $this->assertValidQuery($query, $expected);
    }

    public function testCorrectlyIntrospects()
    {
        $query = '{
          __schema {
            queryType {
              fields {
                name
                args {
                  name
                  type {
                    kind
                    ofType {
                      kind
                      ofType {
                        kind
                        ofType {
                          name
                          kind
                        }
                      }
                    }
                  }
                }
                type {
                  kind
                  ofType {
                    name
                    kind
                  }
                }
              }
            }
          }
        }';
        $expected = ['__schema' =>
            ['queryType' =>
                ['fields' =>
                    [0 =>
                        ['name' => 'usernames', 'args' =>
                            [0 =>
                                ['name' => 'usernames', 'type' =>
                                    ['kind' => 'NON_NULL', 'ofType' =>
                                        ['kind' => 'LIST', 'ofType' =>
                                            ['kind' => 'NON_NULL', 'ofType' =>
                                                ['name' => 'String', 'kind' => 'SCALAR']]]]]], 'type' =>
                            ['kind' => 'LIST', 'ofType' =>
                                ['name' => 'User', 'kind' => 'OBJECT']]]]]]];

        $this->assertValidQuery($query, $expected);
    }

    /**
     * Helper function to test a query and the expected response.
     */
    private function assertValidQuery($query, $expected)
    {
        $result = GraphQL::execute(static::getSchema(), $query, ['lang' => 'en']);
        $this->assertEquals(['data' => $expected], $result);
    }
}
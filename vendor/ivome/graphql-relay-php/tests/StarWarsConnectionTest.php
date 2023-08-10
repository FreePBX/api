<?php
/**
 * @author: Ivo Meißner
 * Date: 29.02.16
 * Time: 12:18
 */

namespace GraphQLRelay\tests;


use GraphQL\GraphQL;

class StarWarsConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testFetchesTheFirstShipOfTheRebels()
    {
        $query = 'query RebelsShipsQuery {
            rebels {
              name,
              ships(first: 1) {
                edges {
                  node {
                    name
                  }
                }
              }
            }
          }';

        $expected = ['rebels' =>
            ['name' => 'Alliance to Restore the Republic', 'ships' =>
                ['edges' =>
                    [0 =>
                        ['node' =>
                            ['name' => 'X-Wing']]]]]];

        $this->assertValidQuery($query, $expected);
    }

    public function testFetchesTheFirstTwoShipsOfTheRebelsWithACursor()
    {
        $query = 'query MoreRebelShipsQuery {
            rebels {
              name,
              ships(first: 2) {
                edges {
                  cursor,
                  node {
                    name
                  }
                }
              }
            }
          }';

        $expected = ['rebels' =>
            ['name' => 'Alliance to Restore the Republic', 'ships' =>
                ['edges' =>
                    [0 =>
                        ['cursor' => 'YXJyYXljb25uZWN0aW9uOjA=', 'node' =>
                            ['name' => 'X-Wing']], 1 =>
                        ['cursor' => 'YXJyYXljb25uZWN0aW9uOjE=', 'node' =>
                            ['name' => 'Y-Wing']]]]]];

        $this->assertValidQuery($query, $expected);
    }

    public function testFetchesTheNextThreeShipsOfTHeRebelsWithACursor()
    {
        $query = 'query EndOfRebelShipsQuery {
            rebels {
              name,
              ships(first: 3 after: "YXJyYXljb25uZWN0aW9uOjE=") {
                edges {
                  cursor,
                  node {
                    name
                  }
                }
              }
            }
          }';

        $expected = ['rebels' =>
            ['name' => 'Alliance to Restore the Republic', 'ships' =>
                ['edges' =>
                    [0 =>
                        ['cursor' => 'YXJyYXljb25uZWN0aW9uOjI=', 'node' =>
                            ['name' => 'A-Wing']], 1 =>
                        ['cursor' => 'YXJyYXljb25uZWN0aW9uOjM=', 'node' =>
                            ['name' => 'Millenium Falcon']], 2 =>
                        ['cursor' => 'YXJyYXljb25uZWN0aW9uOjQ=', 'node' =>
                            ['name' => 'Home One']]]]]];

        $this->assertValidQuery($query, $expected);
    }

    public function testFetchesNoShipsOfTheRebelsAtTheEndOfConnection()
    {
        $query = 'query RebelsQuery {
            rebels {
              name,
              ships(first: 3 after: "YXJyYXljb25uZWN0aW9uOjQ=") {
                edges {
                  cursor,
                  node {
                    name
                  }
                }
              }
            }
          }';

        $expected = ['rebels' =>
            ['name' => 'Alliance to Restore the Republic', 'ships' =>
                ['edges' =>
                    []]]];

        $this->assertValidQuery($query, $expected);
    }

    public function testIdentifiesTheEndOfTheList()
    {
        $query = 'query EndOfRebelShipsQuery {
            rebels {
              name,
              originalShips: ships(first: 2) {
                edges {
                  node {
                    name
                  }
                }
                pageInfo {
                  hasNextPage
                }
              }
              moreShips: ships(first: 3 after: "YXJyYXljb25uZWN0aW9uOjE=") {
                edges {
                  node {
                    name
                  }
                }
                pageInfo {
                  hasNextPage
                }
              }
            }
          }';
        $expected = ['rebels' =>
            ['name' => 'Alliance to Restore the Republic', 'originalShips' =>
                ['edges' =>
                    [0 =>
                        ['node' =>
                            ['name' => 'X-Wing']], 1 =>
                        ['node' =>
                            ['name' => 'Y-Wing']]], 'pageInfo' =>
                    ['hasNextPage' => true]], 'moreShips' =>
                ['edges' =>
                    [0 =>
                        ['node' =>
                            ['name' => 'A-Wing']], 1 =>
                        ['node' =>
                            ['name' => 'Millenium Falcon']], 2 =>
                        ['node' =>
                            ['name' => 'Home One']]], 'pageInfo' =>
                    ['hasNextPage' => false]]]];

        $this->assertValidQuery($query, $expected);
    }

    /**
     * Helper function to test a query and the expected response.
     */
    private function assertValidQuery($query, $expected)
    {
        $result = GraphQL::execute(StarWarsSchema::getSchema(), $query);

        $this->assertEquals(['data' => $expected], $result);
    }
}
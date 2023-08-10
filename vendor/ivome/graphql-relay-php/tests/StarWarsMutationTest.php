<?php
/**
 * @author: Ivo Meißner
 * Date: 29.02.16
 * Time: 12:11
 */

namespace GraphQLRelay\tests;


use GraphQL\GraphQL;

class StarWarsMutationTest extends \PHPUnit_Framework_TestCase
{
    public function testMutatesTheDataSet()
    {
        $mutation = 'mutation AddBWingQuery($input: IntroduceShipInput!) {
            introduceShip(input: $input) {
              ship {
                id
                name
              }
              faction {
                name
              }
              clientMutationId
            }
          }';

        $params = ['input' =>
            ['shipName' => 'B-Wing', 'factionId' => '1', 'clientMutationId' => 'abcde']];

        $expected = ['introduceShip' =>
            ['ship' =>
                ['id' => 'U2hpcDo5', 'name' => 'B-Wing'], 'faction' =>
                ['name' => 'Alliance to Restore the Republic'], 'clientMutationId' => 'abcde']];

        $result = GraphQL::execute(StarWarsSchema::getSchema(), $mutation, null, null, $params);

        $this->assertEquals(['data' => $expected], $result);
    }
}
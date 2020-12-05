<?php

namespace tests\Classes\CategoryTest;

use PHPUnit\Framework\TestCase;
use ProductsImporter\Classes\Category;

class CategoryTest extends TestCase
{

    public function testShouldCreateCategoryTree()
    {
        // GIVEN
        $categoryPaths = [
            ['MAIN', 'SUB_100', 'SUB_110', 'SUB_111'],
            ['MAIN', 'SUB_200', 'SUB_210', 'SUB_211'],
            ['MAIN', 'SUB_100', 'SUB_120', 'SUB_121'],
            ['MAIN', 'SUB_200', 'SUB_210', 'SUB_212'],
            ['MAIN', 'SUB_200', 'SUB_210', 'SUB_213'],
        ];

        // WHEN
        $categoryCollection = Category::importPaths($categoryPaths);

        //Then we suppose the get category tree constructed like that
        $expectedResult = new Category('MAIN');

        $expectedResult->setChilds([
//                // first node
                'SUB_100' => (new Category('SUB_100'))->setChilds([
                    'SUB_110' => (new Category('SUB_110'))->addChild(new Category('SUB_111')),
                    'SUB_120' => (new Category('SUB_120'))->addChild(new Category('SUB_121')),
                ]),

                // second node
                'SUB_200' => (new Category('SUB_200'))->setChilds([
                    'SUB_210' => (new Category('SUB_210'))->setChilds([
                        'SUB_211' => new Category('SUB_211'),
                        'SUB_212' => new Category('SUB_212'),
                        'SUB_213' => new Category('SUB_213'),
                    ]),
                ]),

             ]
        );



        $this->assertEquals(['MAIN' => $expectedResult], $categoryCollection);



    }
}

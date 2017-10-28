<?php


class FirstCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Home');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }
}

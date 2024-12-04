<?php

use App\Social\Statistics\Jam;

it('handles the 0 duration case', function () {
    expect((new Jam)())->toMatchArray(['duration' => '0 seconds']);
});

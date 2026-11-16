<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La página principal lleva al panel administrativo.
     */
    public function test_la_pagina_principal_lleva_al_panel(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }
}

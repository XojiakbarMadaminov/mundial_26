<?php

test('admin can switch the panel locale', function (string $locale) {
    $this->from('/admin')
        ->get(route('admin.locale', ['locale' => $locale]))
        ->assertRedirect('/admin')
        ->assertSessionHas('admin_locale', $locale);
})->with(['en', 'uz']);

test('unsupported admin locale is not found', function () {
    $this->get('/admin/locale/ru')->assertNotFound();
});

<?php

declare(strict_types=1);

namespace TentaPress\Menus\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use TentaPress\Menus\Models\TpMenu;

final class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $menu = $this->route('menu');
        $menuId = $menu instanceof TpMenu ? (int) $menu->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tp_menus', 'slug')->ignore($menuId),
            ],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer', 'exists:tp_menu_items,id'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.url' => ['nullable', 'string', 'max:2048'],
            'items.*.target' => ['nullable', 'string', 'in:_self,_blank'],
            'items.*.parent_id' => ['nullable', 'integer'],
            'items.*.sort_order' => ['nullable', 'integer'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['nullable', 'integer', 'exists:tp_menus,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Menu name is required.',
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, and dashes.',
            'slug.unique' => 'That slug is already in use by another menu.',
            'items.*.title.max' => 'Menu item titles may not be longer than 255 characters.',
            'items.*.url.max' => 'Menu item URLs may not be longer than 2048 characters.',
        ];
    }
}

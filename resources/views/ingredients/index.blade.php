<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestion des Ingrédients') }}
        </h2>
    </x-slot>

    @livewire('ingredient-manager')
</x-app-layout>

<x-filament::page>
  <x-slot name="header">
    <x-filament::page.header :actions="$this->getActions()" />
  </x-slot>

  @foreach ($this->getWidgets() as $widget)
    @livewire($widget)
  @endforeach
</x-filament::page>
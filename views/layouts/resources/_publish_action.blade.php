<td>
    <a href="#" data-behavior="toggle_active" data-toggle-id="{{ $item->id }}" data-toggle-url="{{ moduleRoute($moduleName, $routePrefix, 'publish') }}" class="icon icon-publish {{ ($item->published) ? 'active' : '' }}" title="Publish {{ strtolower($modelName) }}">Publish {{ strtolower($modelName) }}</a>
</td>

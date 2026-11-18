@php($sizeMap = $costume->sizes->keyBy('size'))
@php($isActive = old('is_active', $costume->exists ? $costume->is_active : true))

<fieldset>
    <legend>Informasi Kostum</legend>

    <p>
        <label for="character_name">Nama karakter</label>
        <input id="character_name" name="character_name" type="text" value="{{ old('character_name', $costume->character_name) }}" required>
    </p>

    <p>
        <label for="description">Deskripsi (bahan, kelengkapan, kondisi)</label>
        <textarea id="description" name="description" rows="5" required>{{ old('description', $costume->description) }}</textarea>
    </p>

    <p>
        <label for="accessories">Aksesori (wig, senjata, dan lainnya)</label>
        <textarea id="accessories" name="accessories" rows="3">{{ old('accessories', $costume->accessories) }}</textarea>
    </p>

    <p>
        <label for="category_id">Kategori</label>
        <select id="category_id" name="category_id">
            <option value="">Tanpa kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $costume->category_id) === (string) $category->id)>
                    {{ $category->name }} @if ($category->tenant_id === null)(global)@endif
                </option>
            @endforeach
        </select>
    </p>

    <p>
        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
            @foreach (App\Models\Costume::GENDERS as $value => $label)
                <option value="{{ $value }}" @selected(old('gender', $costume->gender ?? 'unisex') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </p>

    <p>
        <label for="city">Kota</label>
        <input id="city" name="city" type="text" value="{{ old('city', $costume->city ?? auth()->user()->tenant->city) }}" required>
    </p>

    <p>
        <label for="price_per_day">Harga sewa per hari (Rupiah, contoh 150000)</label>
        <input id="price_per_day" name="price_per_day" type="number" min="1000" step="1" value="{{ old('price_per_day', $costume->price_per_day ?? 150000) }}" required>
    </p>

    <p>
        <label for="deposit">Deposit (Rupiah, kosongkan untuk memakai deposit bawaan)</label>
        <input id="deposit" name="deposit" type="number" min="0" step="1" value="{{ old('deposit', $costume->deposit ?? 0) }}">
    </p>

    <p>
        <input type="hidden" name="is_popular" value="0">
        <input id="is_popular" name="is_popular" type="checkbox" value="1" @checked(old('is_popular', $costume->is_popular))>
        <label for="is_popular">Tandai sebagai kostum populer</label>
    </p>

    <p>
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked($isActive)>
        <label for="is_active">Kostum aktif dan tampil di katalog</label>
    </p>
</fieldset>

<fieldset>
    <legend>Stok per Ukuran</legend>

    <p>
        <label for="stock_s">Stok S</label>
        <input id="stock_s" name="stock_s" type="number" min="0" max="999" step="1" value="{{ old('stock_s', $sizeMap->get('S')?->stock ?? 0) }}" required>
    </p>

    <p>
        <label for="stock_m">Stok M</label>
        <input id="stock_m" name="stock_m" type="number" min="0" max="999" step="1" value="{{ old('stock_m', $sizeMap->get('M')?->stock ?? 0) }}" required>
    </p>

    <p>
        <label for="stock_l">Stok L</label>
        <input id="stock_l" name="stock_l" type="number" min="0" max="999" step="1" value="{{ old('stock_l', $sizeMap->get('L')?->stock ?? 0) }}" required>
    </p>

    <p>
        <label for="stock_xl">Stok XL</label>
        <input id="stock_xl" name="stock_xl" type="number" min="0" max="999" step="1" value="{{ old('stock_xl', $sizeMap->get('XL')?->stock ?? 0) }}" required>
    </p>
</fieldset>

<fieldset>
    <legend>Foto Kostum</legend>

    <p>
        <label for="images">Unggah foto (bisa lebih dari satu, maksimal 5, masing-masing 2 MB)</label>
        <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
    </p>

    @if ($costume->exists && $costume->images->isNotEmpty())
        <p>Centang foto yang ingin dihapus:</p>
        <ul>
            @foreach ($costume->images as $image)
                <li>
                    <input id="remove_image_{{ $image->id }}" name="remove_images[]" type="checkbox" value="{{ $image->id }}">
                    <label for="remove_image_{{ $image->id }}">Hapus foto</label>
                    <img src="{{ $image->url() }}" alt="Foto kostum {{ $costume->character_name }}" width="120" height="90">
                </li>
            @endforeach
        </ul>
    @endif
</fieldset>

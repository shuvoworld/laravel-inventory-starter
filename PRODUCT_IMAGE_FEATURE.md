# Product Image Upload Feature

## Overview

A comprehensive product image upload system with automatic optimization, preview functionality, and seamless integration into the product management workflow.

## Features Implemented

### 1. Database Schema
- **Migration**: `2025_10_17_175256_add_image_to_products_table.php`
- Added `image` column (nullable string) to `products` table
- Column positioned after `name` for logical ordering

### 2. Image Optimization Service
- **File**: `app/Services/ImageService.php`
- **Features**:
  - Automatic image resizing (max 800x800px)
  - Maintains aspect ratio
  - Compression for JPEG (85% quality) and PNG (level 8)
  - Supports: JPEG, PNG, GIF, WebP
  - Preserves transparency for PNG/GIF
  - Graceful fallback if GD extension unavailable
  - Unique filename generation using UUID
  - Old image cleanup on update/delete

### 3. Product Model Enhancements
- **File**: `app/Modules/Products/Models/Product.php`
- Added `image` to fillable attributes
- **New Methods**:
  - `getImageUrlAttribute()`: Returns full URL for image
  - `getImageOrPlaceholder()`: Returns image URL or placeholder
  - `deleteImage()`: Deletes image file from storage

### 4. Controller Updates
- **File**: `app/Modules/Products/Http/Controllers/ProductController.php`
- Integrated `ImageService` via dependency injection
- **Create**: Handles new image upload
- **Update**: Handles image replacement and removal
- **Delete**: Cleans up image file when product deleted
- Validates images: max 2MB, accepted formats

### 5. Create Form
- **File**: `app/Modules/Products/resources/views/create.blade.php`
- File input with accept restrictions
- Real-time image preview
- Format and size guidelines
- Added `enctype="multipart/form-data"` to form

### 6. Edit Form
- **File**: `app/Modules/Products/resources/views/edit.blade.php`
- Shows current image with preview
- Upload replacement image with preview
- Remove image button with confirmation
- Visual feedback for image removal
- Added `enctype="multipart/form-data"` to form

### 7. Product Grid (Index)
- **File**: `app/Modules/Products/resources/views/index.blade.php`
- Added "Image" column to DataTable
- Displays optimized thumbnails (50x50px)
- Lazy loading for performance
- Rounded borders for visual appeal
- Shows placeholder for products without images

### 8. Placeholder Image
- **File**: `public/images/product-placeholder.svg`
- Professional SVG placeholder
- Minimal file size
- Clear "No Image" indicator

## File Structure

```
app/
├── Services/
│   └── ImageService.php              # Image upload & optimization
├── Modules/Products/
    ├── Models/
    │   └── Product.php                # Image methods & attributes
    ├── Http/Controllers/
    │   └── ProductController.php      # Image handling in CRUD
    └── resources/views/
        ├── create.blade.php           # Upload form
        ├── edit.blade.php             # Edit with preview
        └── index.blade.php            # Grid with images

public/
├── images/
│   └── product-placeholder.svg        # Placeholder image
└── storage/                           # Symlink to storage/app/public

storage/
└── app/public/
    └── products/                      # Uploaded product images

database/migrations/
└── 2025_10_17_175256_add_image_to_products_table.php
```

## Usage

### Uploading a Product Image

1. **Create New Product**:
   - Navigate to Products → Create
   - Click "Choose File" under "Product Image"
   - Select image (JPEG, PNG, GIF, or WebP)
   - Preview appears automatically
   - Fill other product details
   - Click "Save"

2. **Update Product Image**:
   - Navigate to Products → Edit
   - Current image displayed (if exists)
   - To replace: Choose new file (preview shows)
   - To remove: Click "Remove Image" button
   - Click "Update" to save changes

### Image Requirements
- **Max Size**: 2MB
- **Formats**: JPEG, JPG, PNG, GIF, WebP
- **Recommended**: Square images work best
- **Auto-optimization**: Images automatically resized to max 800x800px

## Technical Details

### Image Processing Flow

1. **Upload**:
   ```
   User selects file → Validation → Upload to storage/app/public/products/
   → Optimization (resize to 800x800) → Save to database
   ```

2. **Update**:
   ```
   User uploads new image → Delete old image → Upload new image
   → Optimization → Update database record
   ```

3. **Remove**:
   ```
   User clicks "Remove Image" → Set hidden field → On submit
   → Delete file from storage → Set image column to NULL
   ```

4. **Delete Product**:
   ```
   Delete product → Delete associated image file → Delete database record
   ```

### Storage Location
- Images stored in: `storage/app/public/products/`
- Accessible via: `public/storage/products/` (symlink)
- Filename format: `{UUID}.{extension}`

### Optimization Details
- **Max Dimensions**: 800x800 pixels (maintains aspect ratio)
- **JPEG Quality**: 85%
- **PNG Compression**: Level 8
- **Transparency**: Preserved for PNG/GIF
- **WebP Support**: Yes (if GD supports it)

### Performance Optimizations
- **Lazy Loading**: Images load as user scrolls
- **Thumbnail Size**: 50x50px in grid for fast rendering
- **Optimized Storage**: Images compressed on upload
- **Efficient Queries**: Only image path stored in database

## Validation Rules

```php
'image' => [
    'nullable',
    'image',
    'mimes:jpeg,jpg,png,gif,webp',
    'max:2048'  // 2MB in kilobytes
]
```

## API Endpoints

### Store Product with Image
```http
POST /modules/products
Content-Type: multipart/form-data

Fields:
- image: (file)
- name: (required)
- sku: (optional)
- unit: (optional)
- price: (optional)
- cost_price: (optional)
- reorder_level: (optional)
```

### Update Product with Image
```http
PUT /modules/products/{id}
Content-Type: multipart/form-data

Fields:
- image: (file, optional - new image)
- remove_image: (boolean, optional - set to 1 to remove)
- Other product fields...
```

## Model Methods

```php
// Get full URL for product image
$product->image_url;
// Returns: "http://example.com/storage/products/uuid.jpg"

// Get image URL or placeholder
$product->getImageOrPlaceholder();
// Returns image URL or placeholder SVG

// Delete product image
$product->deleteImage();
// Returns: true if deleted, false if no image
```

## JavaScript Functions

### Image Preview (Create/Edit)
```javascript
previewImage(event)
```
- Displays selected image before upload
- Shows preview in designated container
- Reads file as Data URL

### Remove Image (Edit Only)
```javascript
removeImage()
```
- Confirms removal with user
- Sets hidden field value
- Provides visual feedback

## Troubleshooting

### Images Not Displaying
**Issue**: Images upload but don't show in grid
**Solution**:
```bash
php artisan storage:link
```
Ensure symlink exists from `public/storage` to `storage/app/public`

### Image Quality Issues
**Issue**: Images appear blurry or pixelated
**Solution**: Upload higher resolution images (minimum 800x800px recommended)

### Upload Fails
**Issue**: File upload returns error
**Possible Causes**:
1. File too large (>2MB)
2. Invalid format
3. Storage permissions issue

**Solution**:
```bash
# Check permissions
chmod -R 775 storage/app/public

# Check disk space
df -h

# Verify PHP upload settings in php.ini
upload_max_filesize = 2M
post_max_size = 2M
```

### GD Extension Not Available
**Issue**: Images upload but not optimized
**Solution**: Optimization gracefully skips if GD unavailable. To enable:
```bash
# Ubuntu/Debian
sudo apt-get install php-gd

# Restart PHP-FPM
sudo service php8.2-fpm restart
```

## Future Enhancements

Possible improvements:
- [ ] Multiple images per product
- [ ] Image gallery with zoom
- [ ] Drag-and-drop upload
- [ ] Crop/rotate before upload
- [ ] WebP conversion for all images
- [ ] CDN integration
- [ ] Image variants (thumbnail, medium, large)
- [ ] Bulk image upload
- [ ] Image from URL

## Security Considerations

- File validation prevents non-image uploads
- UUID filenames prevent path traversal
- Max file size limits DOS attacks
- Storage directory not directly web-accessible
- Images served through symlink only

## Performance Impact

- **Database**: +1 varchar column (minimal)
- **Storage**: ~50-200KB per optimized image
- **Page Load**: Minimal (lazy loading, optimized thumbnails)
- **Upload Time**: ~1-2 seconds per image (includes optimization)

## Browser Compatibility

- **File Input**: All modern browsers
- **Preview**: All browsers with FileReader API (IE10+)
- **Lazy Loading**: All modern browsers (native support)
- **Image Formats**: WebP may not display in older browsers (fallback to PNG/JPEG recommended)

## Testing Checklist

- [x] Upload image on create
- [x] Update image on edit
- [x] Remove image on edit
- [x] Delete image when product deleted
- [x] Display images in grid
- [x] Show placeholder when no image
- [x] Preview before upload
- [x] Validate file types
- [x] Validate file size
- [x] Image optimization works
- [x] Symlink created
- [x] Mobile responsive

## Migration Notes

### For Existing Products
Products created before this feature will show placeholder images. To add images:
1. Edit each product individually, or
2. Bulk update via database (manual process)

### Rolling Back
```bash
# Rollback migration
php artisan migrate:rollback --step=1

# Remove symlink
rm public/storage

# Clear product images
rm -rf storage/app/public/products/*
```

## Support

For issues or questions:
1. Check server error logs: `storage/logs/laravel.log`
2. Verify GD extension: `php -m | grep gd`
3. Check storage permissions: `ls -la storage/app/public`

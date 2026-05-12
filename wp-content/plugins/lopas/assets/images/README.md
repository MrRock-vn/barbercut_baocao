# LOPAS Images

## Required Images

Place the following images in this directory:

1. **hero.jpg** - Hero section background image (1600x900px recommended)
   - Use a barber shop or salon image
   - Example: https://images.unsplash.com/photo-1621605815971-fbc98d665033

## Optional Images

The homepage will use Unsplash images as fallback if local images are not available.

## Image Sources

You can copy images from the barber-spa project:
- `barber-spa/public/images/hero.jpg`
- `barber-spa/public/images/salon*.jpg`
- `barber-spa/public/images/service*.jpg`
- `barber-spa/public/images/angel*.jpg`
- `barber-spa/public/images/promo*.jpg`

## Usage

Images are referenced in the code as:
```php
plugins_url('lopas/assets/images/hero.jpg')
```

If images are missing, the system will fallback to Unsplash URLs.

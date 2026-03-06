**Installation**: 

1. Install [Node](https://nodejs.org/en/)
2. Install gulp cli `npm install --global gulp-cli`
3. Install dependencies: `npm install`
4. Run `gulp watch`

**Usage**:

- `gulp` (default) watches sass files in `/sass` and runs `sass:main`, `sass:blocks`, and `sass:admin`
- `sass:main` 
  - files: `/sass/style.scss`
  - destination: `(root dir) style.css`
- `sass:blocks`
  - files: `/sass/blocks.scss`
  - destination: `css/blocks.css`
- `sass:admin`
  - files: `/sass/admin.scss`
  - destination: `css/admin.css`

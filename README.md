Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur  mollis velit quis convallis tempor. Sed pulvinar massa nec odio  elementum, sed mollis ipsum tincidunt.

**Installation**: 

1. Install [Node](https://nodejs.org/en/)
2. Install Gulp: `npm install -g gulp`
3. Install dependencies: `npm install`

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
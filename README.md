# Stockflow

- [Stockflow](#stockflow)
  - [Getting Started](#getting-started)
  - [License](#license)
  - [Credits](#credits)

## Getting Started

1. Build CSS assets:

   ```console
   php bin/console tailwind:build
   ```

2. Create a development user:

   ```console
   php bin/console app:dev:user:create
   ```

   Default credentials: `dev@example.com` / `stockflow-dev`

3. Open `https://localhost/login` in your browser.

Optional: after cloning, install Node.js dependencies if you need ESLint or Prettier:

```console
npm install
```

## License

This project is licensed under the [MIT License](./LICENSE).

## Credits

Stockflow is derived from [Symfony Docker](https://github.com/dunglas/symfony-docker) by [Kévin Dunglas](https://dunglas.dev) and contributors. The upstream template readme is kept in [README.symfony-docker.md](./README.symfony-docker.md).

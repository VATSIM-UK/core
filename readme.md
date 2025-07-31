# Core

Core application for VATSIM UK
[www.vatsim.uk](https://vatsim.uk)

## Getting Started

After cloning the repository:

```bash
composer install
composer install-hooks  # Install Git hooks for code quality
```

## Contributing

If you wish to contribute, take a look at:
- [Our general contributing guide](https://github.com/VATSIM-UK/core/blob/master/.github/CONTRIBUTING.md)
- [The first time contributor's guide](https://github.com/VATSIM-UK/core/blob/main/.github/FIRST%20TIME%20CONTRIBUTORS.md)

### Code Quality

This project uses Git hooks to maintain code quality:
- **Pre-commit hook**: Automatically runs `composer lint` to check code style
- All hooks are stored in `.development/hooks/` and can be installed with `composer install-hooks`

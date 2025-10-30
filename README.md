# Agunan

A simple and lightweight collateral management system for Node.js.

**Agunan** (Indonesian for "collateral") is a library that helps you manage collateral assets in financial applications. It provides a simple API for tracking, updating, and managing collateral records.

## Features

- ✅ Add, update, and remove collateral records
- ✅ Track collateral value and status (active, released, liquidated)
- ✅ Support for multiple collateral types (real estate, vehicles, securities, cash, etc.)
- ✅ Filter collaterals by type and status
- ✅ Calculate total collateral values
- ✅ Simple and intuitive API

## Installation

```bash
npm install agunan
```

## Usage

### Basic Example

```javascript
const { CollateralManager } = require('agunan');

// Create a new collateral manager
const manager = new CollateralManager();

// Add collateral assets
manager.addCollateral('C001', 'real-estate', 500000, 'House in Jakarta');
manager.addCollateral('C002', 'vehicle', 200000, 'Car');
manager.addCollateral('C003', 'securities', 300000, 'Stocks');

// Get a specific collateral
const collateral = manager.getCollateral('C001');
console.log(collateral);

// Update collateral value
manager.updateCollateralValue('C001', 550000);

// Update collateral status
manager.updateCollateralStatus('C002', 'released');

// Get all active collaterals
const activeCollaterals = manager.getCollateralsByStatus('active');
console.log('Active collaterals:', activeCollaterals);

// Calculate total active value
const totalValue = manager.getTotalActiveValue();
console.log('Total active value:', totalValue);

// Get collaterals by type
const realEstate = manager.getCollateralsByType('real-estate');
console.log('Real estate collaterals:', realEstate);

// Remove a collateral
manager.removeCollateral('C003');
```

## API Reference

### CollateralManager

#### Methods

- `addCollateral(id, type, value, description)` - Add a new collateral
- `getCollateral(id)` - Get a collateral by ID
- `getAllCollaterals()` - Get all collaterals
- `getCollateralsByStatus(status)` - Get collaterals by status ('active', 'released', 'liquidated')
- `getCollateralsByType(type)` - Get collaterals by type
- `updateCollateralValue(id, newValue)` - Update the value of a collateral
- `updateCollateralStatus(id, newStatus)` - Update the status of a collateral
- `removeCollateral(id)` - Remove a collateral
- `getTotalActiveValue()` - Get total value of all active collaterals
- `getTotalValue()` - Get total value of all collaterals
- `getCount()` - Get the count of collaterals

### Collateral

#### Properties

- `id` - Unique identifier
- `type` - Type of collateral (e.g., 'real-estate', 'vehicle', 'securities', 'cash')
- `value` - Current value of the collateral
- `description` - Description of the collateral
- `status` - Status of the collateral ('active', 'released', 'liquidated')
- `createdAt` - Creation timestamp
- `updatedAt` - Last update timestamp

#### Methods

- `updateValue(newValue)` - Update the value
- `updateStatus(newStatus)` - Update the status
- `toJSON()` - Get collateral as a plain object

## Testing

Run the test suite:

```bash
npm test
```

## License

MIT

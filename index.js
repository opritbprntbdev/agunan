/**
 * Collateral Class
 * Represents a single collateral asset
 */
class Collateral {
  constructor(id, type, value, description = '') {
    if (!id || typeof id !== 'string') {
      throw new Error('ID is required and must be a string');
    }
    if (!type || typeof type !== 'string') {
      throw new Error('Type is required and must be a string');
    }
    if (typeof value !== 'number' || value < 0) {
      throw new Error('Value must be a non-negative number');
    }
    this.id = id;
    this.type = type; // e.g., 'real-estate', 'vehicle', 'securities', 'cash'
    this.value = value;
    this.description = description;
    this.status = 'active'; // 'active', 'released', 'liquidated'
    this.createdAt = new Date();
    this.updatedAt = new Date();
  }

  /**
   * Update the value of the collateral
   */
  updateValue(newValue) {
    if (typeof newValue !== 'number' || newValue < 0) {
      throw new Error('Value must be a non-negative number');
    }
    this.value = newValue;
    this.updatedAt = new Date();
  }

  /**
   * Update the status of the collateral
   */
  updateStatus(newStatus) {
    const validStatuses = ['active', 'released', 'liquidated'];
    if (!validStatuses.includes(newStatus)) {
      throw new Error(`Invalid status. Must be one of: ${validStatuses.join(', ')}`);
    }
    this.status = newStatus;
    this.updatedAt = new Date();
  }

  /**
   * Get collateral information as a plain object
   */
  toJSON() {
    return {
      id: this.id,
      type: this.type,
      value: this.value,
      description: this.description,
      status: this.status,
      createdAt: this.createdAt,
      updatedAt: this.updatedAt
    };
  }
}

/**
 * CollateralManager Class
 * Manages multiple collateral assets
 */
class CollateralManager {
  constructor() {
    this.collaterals = new Map();
  }

  /**
   * Add a new collateral
   */
  addCollateral(id, type, value, description = '') {
    if (this.collaterals.has(id)) {
      throw new Error(`Collateral with id ${id} already exists`);
    }
    const collateral = new Collateral(id, type, value, description);
    this.collaterals.set(id, collateral);
    return collateral;
  }

  /**
   * Get a collateral by id
   */
  getCollateral(id) {
    return this.collaterals.get(id);
  }

  /**
   * Get all collaterals
   */
  getAllCollaterals() {
    return Array.from(this.collaterals.values());
  }

  /**
   * Get collaterals by status
   */
  getCollateralsByStatus(status) {
    return this.getAllCollaterals().filter(c => c.status === status);
  }

  /**
   * Get collaterals by type
   */
  getCollateralsByType(type) {
    return this.getAllCollaterals().filter(c => c.type === type);
  }

  /**
   * Update collateral value
   */
  updateCollateralValue(id, newValue) {
    const collateral = this.getCollateral(id);
    if (!collateral) {
      throw new Error(`Collateral with id ${id} not found`);
    }
    collateral.updateValue(newValue);
    return collateral;
  }

  /**
   * Update collateral status
   */
  updateCollateralStatus(id, newStatus) {
    const collateral = this.getCollateral(id);
    if (!collateral) {
      throw new Error(`Collateral with id ${id} not found`);
    }
    collateral.updateStatus(newStatus);
    return collateral;
  }

  /**
   * Remove a collateral
   */
  removeCollateral(id) {
    if (!this.collaterals.has(id)) {
      throw new Error(`Collateral with id ${id} not found`);
    }
    return this.collaterals.delete(id);
  }

  /**
   * Get total value of all active collaterals
   */
  getTotalActiveValue() {
    return this.getCollateralsByStatus('active')
      .reduce((sum, c) => sum + c.value, 0);
  }

  /**
   * Get total value of all collaterals
   */
  getTotalValue() {
    return this.getAllCollaterals()
      .reduce((sum, c) => sum + c.value, 0);
  }

  /**
   * Get count of collaterals
   */
  getCount() {
    return this.collaterals.size;
  }
}

module.exports = { Collateral, CollateralManager };

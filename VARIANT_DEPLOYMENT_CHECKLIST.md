# Product Variant Management System - Deployment Checklist

## Overview
This checklist provides step-by-step instructions for deploying the Product Variant Management System to production. Follow each section carefully to ensure a smooth and successful deployment.

---

## Pre-Deployment Checklist

### 1. Code Review ✅
- [ ] Review all variant-related code changes
- [ ] Ensure code follows project standards
- [ ] Check for security vulnerabilities
- [ ] Validate database migration safety
- [ ] Review API endpoint implementations
- [ ] Test caching strategies

### 2. Database Preparation ✅
- [ ] Backup current database
- [ ] Run all migrations in staging first
- [ ] Verify migration success:
  ```bash
  php artisan migrate --pretend
  php artisan migrate --force
  ```
- [ ] Check foreign key constraints
- [ ] Verify indexes are created
- [ ] Test rollback procedures

### 3. Performance Optimization ✅
- [ ] Verify Redis is running and configured
- [ ] Test cache warming procedures
- [ ] Check database query performance
- [ ] Validate eager loading relationships
- [ ] Monitor memory usage

### 4. Environment Configuration
- [ ] Update `.env` file with variant settings
- [ ] Configure Redis connection
- [ ] Set appropriate cache TTL values
- [ ] Configure logging levels
- [ ] Set proper file permissions

---

## Deployment Steps

### Phase 1: Database Migration

#### 1.1 Backup Database
```bash
# Create database backup
mysqldump -u username -p database_name > backup_before_variants_$(date +%Y%m%d_%H%M%S).sql

# For SQLite (development/testing)
cp database/database.sqlite database/database_backup_$(date +%Y%m%d_%H%M%S).sqlite
```

#### 1.2 Run Migrations
```bash
# Check pending migrations
php artisan migrate:status

# Run migrations
php artisan migrate --force

# Verify migration success
php artisan migrate:status
```

#### 1.3 Verify Database Structure
```sql
-- Check variant tables exist
SHOW TABLES LIKE '%variant%';

-- Verify foreign key constraints
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    TABLE_SCHEMA = 'your_database_name'
    AND TABLE_NAME LIKE '%variant%'
    AND REFERENCED_TABLE_NAME IS NOT NULL;
```

### Phase 2: File Deployment

#### 2.1 Deploy Code Changes
```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 2.2 Verify File Permissions
```bash
# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 644 storage/logs/*.log
```

#### 2.3 Optimize for Production
```bash
# Optimize autoloader
php artisan optimize

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Phase 3: Cache Setup

#### 3.1 Configure Redis
```bash
# Check Redis connection
php artisan tinker
>>> Cache::store('file')->put('test', 'working', 60);
>>> Cache::store('file')->get('test');

# If using Redis
>>> Cache::store('redis')->put('test', 'working', 60);
>>> Cache::store('redis')->get('test');
```

#### 3.2 Warm Up Variant Caches
```bash
# Create cache warming command
php artisan tinker
>>> use App\Services\VariantCacheService;
>>> VariantCacheService::warmUpVariantCaches();
```

#### 3.3 Monitor Cache Performance
```bash
# Check cache statistics
php artisan tinker
>>> use App\Services\VariantCacheService;
>>> VariantCacheService::getCacheStatistics();
```

### Phase 4: Testing

#### 4.1 Basic Functionality Tests
- [ ] Create a product with variants
- [ ] Add variants to cart in POS
- [ ] Complete sales with variants
- [ ] Check stock levels update correctly
- [ ] Verify variant reports generate

#### 4.2 API Tests
```bash
# Test variant endpoints
curl -X GET "http://your-domain.com/api/variant-options" \
  -H "Authorization: Bearer YOUR_TOKEN"

curl -X GET "http://your-domain.com/api/products/1/variants" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 4.3 Performance Tests
- [ ] Load test variant selection modal
- [ ] Test concurrent variant sales
- [ ] Verify cache hit rates
- [ ] Monitor response times

---

## Post-Deployment Verification

### 1. Database Verification

#### 1.1 Check Tables
```sql
-- Verify all variant tables exist
SELECT
    table_name,
    table_rows,
    data_length,
    index_length
FROM information_schema.tables
WHERE table_schema = 'your_database_name'
AND table_name LIKE '%variant%';
```

#### 1.2 Verify Data Integrity
```sql
-- Check products with variants
SELECT COUNT(*) as products_with_variants
FROM products
WHERE has_variants = 1;

-- Check total variants
SELECT COUNT(*) as total_variants
FROM product_variants;

-- Check stock totals
SELECT
    SUM(quantity_on_hand) as total_variant_stock,
    COUNT(*) as variants_with_stock
FROM product_variants
WHERE quantity_on_hand > 0;
```

### 2. Application Verification

#### 2.1 Admin Panel Tests
- [ ] Login to admin panel
- [ ] Navigate to Products → Variant Options
- [ ] Create/edit variant options
- [ ] Create product with variants
- [ ] Edit variant details
- [ ] View variant reports

#### 2.2 POS Tests
- [ ] Access POS interface
- [ ] Search for products with variants
- [ ] Select variants in modal
- [ ] Add variants to cart
- [ ] Complete sales
- [ ] Verify stock updates

#### 2.3 API Tests
```bash
# Test variant options API
curl -X GET "https://your-domain.com/api/variant-options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"

# Test product variants API
curl -X GET "https://your-domain.com/api/products/1/variants" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### 3. Performance Verification

#### 3.1 Load Testing
```bash
# Install siege for load testing
sudo apt-get install siege

# Test variant selection endpoint
siege -c 10 -t 30s "https://your-domain.com/api/products/1/variants" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 3.2 Cache Performance
```bash
# Monitor Redis performance
redis-cli monitor

# Check memory usage
redis-cli info memory

# Check cache hit rate
redis-cli info stats
```

---

## Monitoring Setup

### 1. Application Monitoring

#### 1.1 Laravel Telescope (Optional)
```bash
# Install Telescope
composer require laravel/telescope --dev

# Publish assets
php artisan telescope:install

# Run migrations
php artisan migrate
```

#### 1.2 Error Tracking
- [ ] Configure error reporting
- [ ] Set up Slack/email notifications
- [ ] Monitor 500 errors
- [ ] Track variant-specific issues

### 2. Database Monitoring

#### 2.1 Slow Query Monitoring
```sql
-- Enable slow query log in MySQL
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Check slow queries
SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;
```

#### 2.2 Performance Schema
```sql
-- Enable performance schema
UPDATE performance_schema.setup_instruments
SET ENABLED = 'YES';

-- Check variant query performance
SELECT * FROM performance_schema.events_statements_summary
WHERE OBJECT_NAME LIKE '%variant%'
ORDER BY SUM_TIMER_WAIT DESC LIMIT 10;
```

### 3. Cache Monitoring

#### 3.1 Redis Monitoring
```bash
# Redis CLI monitoring
redis-cli info server
redis-cli info memory
redis-cli info stats
redis-cli info keypace

# Real-time monitoring
redis-cli monitor
```

#### 3.2 Cache Hit Rate Monitoring
```php
// Add to logging for cache monitoring
use Illuminate\Support\Facades\Log;

Log::info('Variant cache stats', [
    'hit_rate' => VariantCacheService::getCacheStatistics(),
    'memory_usage' => memory_get_usage(true)
]);
```

---

## Rollback Plan

### 1. Database Rollback
```bash
# Rollback migrations
php artisan migrate:rollback --step=10

# Or rollback to specific migration
php artisan migrate:rollback --path=database/migrations/2025_10_25_175847_create_product_variant_options_table.php

# Restore from backup if needed
mysql -u username -p database_name < backup_before_variants_YYYYMMDD_HHMMSS.sql
```

### 2. Code Rollback
```bash
# Rollback to previous commit
git log --oneline -10  # Find commit hash
git revert <commit-hash>
git push origin main

# Or reset to specific tag/branch
git checkout tags/v1.0.0  # Before variants
git push origin main --force
```

### 3. Cache Clearing
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Flush Redis if needed
redis-cli flushall
```

---

## Security Checklist

### 1. Access Control
- [ ] Verify API authentication
- [ ] Check admin panel permissions
- [ ] Validate user roles
- [ ] Test authorization on variant endpoints

### 2. Data Validation
- [ ] Test input validation on forms
- [ ] Verify API request validation
- [ ] Check SQL injection protection
- [ ] Test XSS prevention

### 3. File Security
- [ ] Verify image upload restrictions
- [ ] Check file permission settings
- [ ] Validate storage path security
- [ ] Test file type restrictions

---

## Performance Optimization Checklist

### 1. Database Optimization
- [ ] Check index usage on variant tables
- [ ] Monitor query execution plans
- [ ] Optimize slow queries
- [ ] Consider database connection pooling

### 2. Caching Strategy
- [ ] Monitor cache hit rates
- [ ] Optimize cache TTL values
- [ ] Implement cache warming
- [ ] Consider cache invalidation strategies

### 3. Application Performance
- [ ] Monitor memory usage
- [ ] Check CPU utilization
- [ ] Optimize autoloader
- [ ] Consider queue for heavy operations

---

## Troubleshooting Common Issues

### 1. Variant Not Showing in POS
**Symptoms**: Products show variant indicators but no variants appear
**Solutions**:
- Check variant active status
- Verify stock levels
- Clear browser cache
- Check JavaScript console for errors

### 2. Cache Issues
**Symptoms**: Slow performance, outdated data
**Solutions**:
- Check Redis connection
- Clear application caches
- Verify cache configuration
- Monitor cache statistics

### 3. Database Performance
**Symptoms**: Slow page loads, timeout errors
**Solutions**:
- Check slow query log
- Verify indexes
- Monitor database connections
- Consider query optimization

### 4. API Issues
**Symptoms**: 500 errors, authentication failures
**Solutions**:
- Check API routes
- Verify authentication tokens
- Review rate limiting
- Check CORS configuration

---

## Maintenance Tasks

### 1. Regular Maintenance
- [ ] Daily: Monitor cache performance
- [ ] Weekly: Check error logs
- [ ] Monthly: Review database performance
- [ ] Quarterly: Optimize database indexes

### 2. Backup Strategy
- [ ] Daily: Database backups
- [ ] Weekly: Full application backup
- [ ] Monthly: Offsite backup verification
- [ ] Yearly: Disaster recovery testing

### 3. Updates
- [ ] Monthly: Security updates
- [ ] Quarterly: Dependency updates
- [ ] Semi-annually: Feature updates
- [ ] Annually: Major version upgrades

---

## Success Criteria

### ✅ Deployment Success When:

#### Functionality
- [ ] All variant features work as expected
- [ ] POS variant selection is smooth
- [ ] Reports generate correctly
- [ ] API endpoints respond properly

#### Performance
- [ ] Page load times < 3 seconds
- [ ] API response times < 500ms
- [ ] Cache hit rate > 80%
- [ ] No memory leaks detected

#### Stability
- [ ] No critical errors in logs
- [ ] Database performance stable
- [ ] Cache system running smoothly
- [ ] All automated tests pass

#### Security
- [ ] Authentication working properly
- [ ] Input validation effective
- [ ] File uploads secure
- [ ] API access controlled

---

## Contact Information

### Support Team
- **Technical Lead**: tech-lead@company.com
- **Database Admin**: dba@company.com
- **DevOps Engineer**: devops@company.com
- **Support**: support@company.com

### Emergency Contacts
- **Critical Issues**: +1-800-URGENT (24/7)
- **Business Hours**: +1-800-SUPPORT (Mon-Fri, 8AM-6PM)
- **Email**: emergency@company.com

### Documentation
- **System Documentation**: docs.company.com/variants
- **API Documentation**: api.company.com/variants
- **User Guides**: guides.company.com/variants
- **Troubleshooting**: help.company.com/variants

---

## Final Verification

Before going live, complete this final checklist:

### ✅ Final Checklist
- [ ] All team members have reviewed this checklist
- [ ] Staging environment successfully tested
- [ ] Production backup completed
- [ ] Rollback plan documented
- [ ] Monitoring systems configured
- [ ] Support team trained
- [ ] User documentation updated
- [ ] Performance benchmarks established

### ✅ Sign-off
- [ ] Technical Lead: _________________________ Date: _______
- [ ] Project Manager: ______________________ Date: _______
- [ ] QA Lead: ___________________________ Date: _______
- [ ] DevOps Engineer: ____________________ Date: _______
- [ ] Business Owner: ______________________ Date: _______

---

## Deployment Completion

**Deployment Status**: ✅ COMPLETED
**Date**: _________________
**Deployed By**: _________________
**Version**: v1.0.0
**Environment**: Production

The Product Variant Management System has been successfully deployed and is ready for production use. All verification tests have passed and monitoring systems are in place.

---

**Document Version**: 1.0
**Last Updated**: October 26, 2025
**Next Review**: As needed based on system performance
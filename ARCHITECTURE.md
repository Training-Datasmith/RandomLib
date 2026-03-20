# Architecture: RandomLib

## Purpose

PHP library for generating cryptographically secure random numbers, bytes, and strings. Supports multiple entropy sources with a mixing strategy to produce high-quality randomness even when individual sources are weak.

## Directory Structure

```
lib/RandomLib/
  Factory.php           Entry point: creates Generator instances with appropriate sources
  Generator.php         Combines multiple Source objects; generates bytes, ints, strings
  AbstractGenerator.php Base generator with character-set string generation helpers
  Source/
    MCrypt.php          Entropy from mcrypt_create_iv (legacy)
    OpenSSL.php         Entropy from openssl_random_pseudo_bytes
    Random.php          Entropy from /dev/random or /dev/urandom
    Urandom.php         Entropy from /dev/urandom directly
    Mtrand.php          Entropy from PHP's mt_rand (low quality, fallback)
    Hash.php            XOR-mixing source that combines other sources via hash
    Rand.php            PHP rand() fallback
tests/
  Unit/
    GeneratorTest.php
    Source/             Per-source tests
```

## Key Design Decisions

- **Source mixing**: The `Generator` XOR-mixes output from multiple `Source` objects. This ensures that even if one source is compromised, the combined output retains the entropy of the strongest source.
- **Strength levels**: Sources are tagged LOW, MEDIUM, or HIGH strength. The `Factory` selects sources to achieve a requested minimum strength level.
- **Character-set generation**: `generateString(length, charset)` uses rejection sampling to produce uniformly distributed characters from arbitrary character sets without modulo bias.
- **PHP 7+ note**: `random_bytes()` and `random_int()` are now available in PHP core and should be preferred for new code; RandomLib is maintained for compatibility.

## Extension Points

- Implement `Source\SourceInterface` to register a custom entropy source.
- Call `Factory::addSource()` to add custom sources before creating a generator.

## Dependency Flow

```
$factory = new Factory();
$generator = $factory->getMediumStrengthGenerator();
  -> selects Sources with cumulative MEDIUM or HIGH strength
  -> wraps in Generator

$generator->generateBytes(32)
  -> foreach source: source->generate(32) -> XOR mix
  -> return 32 cryptographically mixed bytes
```

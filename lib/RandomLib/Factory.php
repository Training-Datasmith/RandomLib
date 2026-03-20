<?php

declare (strict_types=1);
/*
 * The RandomLib library for securely generating random numbers and strings in PHP
 *
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright  2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    Build @@version@@
 */
/**
 * The Random Factory
 *
 * Use this factory to instantiate random number generators, sources and mixers.
 *
 * PHP version 5.3
 *
 * @category   PHPPasswordLib
 * @package    Random
 *
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright  2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * @version    Build @@version@@
 */
namespace Random_Lib;

use Security_Lib\Strength;
/**
 * The Random Factory
 *
 * Use this factory to instantiate random number generators, sources and mixers.
 *
 * @category   PHPPasswordLib
 * @package    Random
 *
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 */
class Factory extends \Security_Lib\Abstract_Factory
{
    /**
     * @var array A list of available random number mixing strategies
     */
    protected $mixers = [];
    /**
     * @var array A list of available random number sources
     */
    protected $sources = [];
    /**
     * Build a new instance of the factory, loading core mixers and sources
     */
    public function __construct()
    {
        $this->load_mixers();
        $this->load_sources();
    }
    /**
     * Get a generator for the requested strength
     *
     * @param Strength $strength The requested strength of the random number
     *
     * @throws RuntimeException If an appropriate mixing strategy isn't found
     *
     * @return Generator The instantiated generator
     */
    public function get_generator(\Security_Lib\Strength $strength): Generator
    {
        $sources = $this->find_sources($strength);
        $mixer = $this->find_mixer($strength);
        return new Generator($sources, $mixer);
    }
    /**
     * Get a high strength random number generator
     *
     * High Strength keys should ONLY be used for generating extremely strong
     * cryptographic keys.  Generating them is very resource intensive and may
     * take several minutes or more depending on the requested size.
     *
     * @return Generator The instantiated generator
     */
    public function get_high_strength_generator(): Generator
    {
        return $this->get_generator(new Strength(Strength::HIGH));
    }

    /**
     * Get a low strength random number generator.
     *
     * Low Strength should be used anywhere that random strings are needed in a
     * non-cryptographical setting.  They are not strong enough to be used as
     * keys or salts.  They are however useful for one-time use tokens.
     *
     * @return Generator A low-strength generator suitable for non-cryptographic tokens
     */
    public function get_low_strength_generator(): Generator
    {
        return $this->get_generator(new Strength(Strength::LOW));
    }

    /**
     * Get a medium strength random number generator.
     *
     * Medium Strength should be used for most needs of a cryptographic nature.
     * They are strong enough to be used as keys and salts.  However, they do
     * take some time and resources to generate, so they should not be over-used.
     *
     * @return Generator A medium-strength generator suitable for session tokens and salts
     */
    public function get_medium_strength_generator(): Generator
    {
        return $this->get_generator(new Strength(Strength::MEDIUM));
    }

    /**
     * Get all loaded mixing strategies.
     *
     * @return array<string, Mixer> Registered mixer instances keyed by name
     */
    public function get_mixers(): array
    {
        return $this->mixers;
    }

    /**
     * Get all loaded random number sources.
     *
     * @return array<string, Source> Registered source instances keyed by name
     */
    public function get_sources(): array
    {
        return $this->sources;
    }

    /**
     * Registers a mixing strategy for this factory instance.
     *
     * @param string $name  Short name for the mixer (used for lookup)
     * @param string $class Fully-qualified class name implementing Mixer
     *
     * @return static Fluent interface
     */
    public function register_mixer(string $name, string $class): static
    {
        $this->register_type('mixers', __NAMESPACE__ . '\Mixer', $name, $class);
        return $this;
    }

    /**
     * Registers a random number source for this factory instance.
     *
     * The class must implement the Source interface.
     *
     * @param string $name  Short name for the source (used for lookup)
     * @param string $class Fully-qualified class name implementing Source
     *
     * @return static Fluent interface
     */
    public function register_source(string $name, string $class): static
    {
        $this->register_type('sources', __NAMESPACE__ . '\Source', $name, $class);
        return $this;
    }
    /**
     * Find a sources based upon the requested strength
     *
     * @param Strength $strength The strength mixer to find
     *
     * @throws RuntimeException if a valid source cannot be found
     *
     * @return Source The found source
     */
    protected function find_sources(\Security_Lib\Strength $strength)
    {
        $sources = [];
        foreach ($this->get_sources() as $source) {
            if ($strength->compare($source::get_strength()) <= 0 && $source::is_supported()) {
                $sources[] = new $source();
            }
        }
        if (0 === count($sources)) {
            throw new \RuntimeException('Could not find sources');
        }
        return $sources;
    }
    /**
     * Find a mixer based upon the requested strength
     *
     * @param Strength $strength The strength mixer to find
     *
     * @throws RuntimeException if a valid mixer cannot be found
     *
     * @return Mixer The found mixer
     */
    protected function find_mixer(\Security_Lib\Strength $strength)
    {
        $new_mixer = null;
        $fallback = null;
        foreach ($this->get_mixers() as $mixer) {
            if (!$mixer::test()) {
                continue;
            }
            if ($strength->compare($mixer::get_strength()) == 0) {
                $new_mixer = new $mixer();
            } elseif ($strength->compare($mixer::get_strength()) == 1) {
                $fallback = new $mixer();
            }
        }
        if (is_null($new_mixer)) {
            if (is_null($fallback)) {
                throw new \RuntimeException('Could not find mixer');
            }
            return $fallback;
        }
        return $new_mixer;
    }
    /**
     * Load all core mixing strategies
     *
     * @return void
     */
    protected function load_mixers()
    {
        $this->load_files(__DIR__ . '/Mixer', __NAMESPACE__ . '\Mixer\\', [$this, 'registerMixer']);
    }
    /**
     * Load all core random number sources
     *
     * @return void
     */
    protected function load_sources()
    {
        $this->load_files(__DIR__ . '/Source', __NAMESPACE__ . '\Source\\', [$this, 'registerSource']);
    }
}
<?php

declare(strict_types=1);

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

final class ReadonlyClassFixer extends AbstractFixer
{
    private const PROPERTY_TOKEN_KINDS = [
        \T_PUBLIC,
        \T_PROTECTED,
        \T_PRIVATE,
        \T_STATIC,
        \T_ABSTRACT,
        \T_FINAL,
        \T_VAR,
        \T_STRING,
        \T_NS_SEPARATOR,
        \T_ARRAY,
        CT::T_ARRAY_TYPEHINT,
        CT::T_NULLABLE_TYPE,
        CT::T_TYPE_ALTERNATION,
        CT::T_TYPE_INTERSECTION,
        CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN,
        CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE,
        CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC,
        CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED,
        CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE,
        FCT::T_READONLY,
        FCT::T_PRIVATE_SET,
        FCT::T_PROTECTED_SET,
        FCT::T_PUBLIC_SET,
    ];

    public function getName(): string
    {
        return 'Custom/readonly_class';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Classes where all properties are readonly must be declared as readonly class.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class Money
                        {
                            public function __construct(
                                public readonly int $amount,
                                public readonly string $currency,
                            ) {}
                        }
                        PHP,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return \PHP_VERSION_ID >= 8_02_00 && $tokens->isAnyTokenKindsFound([\T_CLASS]);
    }

    /**
     * Must run before NoRedundantReadonlyPropertyFixer so it cleans up afterwards.
     */
    public function getPriority(): int
    {
        return 4;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);
        $elements = $tokensAnalyzer->getClassyElements();

        $processedClasses = [];

        foreach ($elements as $element) {
            $classIndex = $element['classIndex'];

            if (isset($processedClasses[$classIndex])) {
                continue;
            }

            $processedClasses[$classIndex] = true;

            if (!$tokens[$classIndex]->isGivenKind(\T_CLASS)) {
                continue;
            }

            $modifiers = $tokensAnalyzer->getClassyModifiers($classIndex);

            if (null !== $modifiers['readonly'] || null !== $modifiers['abstract']) {
                continue;
            }

            $classProperties = array_filter(
                $elements,
                static fn (array $el): bool => $el['classIndex'] === $classIndex
                    && \in_array($el['type'], ['property', 'promoted_property'], true),
            );

            if ([] === $classProperties) {
                continue;
            }

            $allReadonly = true;

            foreach ($classProperties as $propIndex => $propElement) {
                if (!$this->hasReadonlyModifier($tokens, $propIndex)) {
                    $allReadonly = false;

                    break;
                }
            }

            if ($allReadonly) {
                $this->insertReadonlyBeforeClass($tokens, $classIndex);
            }
        }
    }

    private function hasReadonlyModifier(Tokens $tokens, int $variableIndex): bool
    {
        $index = $tokens->getPrevMeaningfulToken($variableIndex);

        while ($tokens[$index]->isGivenKind(self::PROPERTY_TOKEN_KINDS) || $tokens[$index]->equals('&')) {
            if ($tokens[$index]->isGivenKind(FCT::T_READONLY)) {
                return true;
            }
            $index = $tokens->getPrevMeaningfulToken($index);
        }

        return false;
    }

    private function insertReadonlyBeforeClass(Tokens $tokens, int $classIndex): void
    {
        $tokens->insertAt($classIndex, [
            new Token([FCT::T_READONLY, 'readonly']),
            new Token([\T_WHITESPACE, ' ']),
        ]);
    }
}

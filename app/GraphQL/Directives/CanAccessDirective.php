<?php

namespace App\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CanAccessDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        // TODO: Implement definition() method.
        return /** @lang GraphQL */ <<<GRAPHQL
            """
            limit field access to users of a certain role.
            """
            directive @canAcess(
            """
            The name of the role authorized users need to have
            """
            requiredRole: String!
            ) on FIELD_DEFINITION
            GRAPHQL;

    }

    /**
     * Wrap around the final field resolver.
     *
     * @param FieldValue $fieldValue
     * @param Closure $next
     * @return FieldValue
     */
    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $originalResolver =$fieldValue->getResolver();
        return  $next(
            $fieldValue->setResolver(
                function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use($originalResolver){
                    $requiredRole = $this->directiveArgValue('requiredRole');
                    // Throw in case of invalid schema definition to remind the developer
                    if ($requiredRole === null)
                        throw new DefinitionException("Missing argument 'requiredRole' for directive '@canAccess'.");
                $user =$context->user();
                // TODO: Replace with https://spatie.be/docs/laravel-permission
                if (!$user || $user->role !== $requiredRole){
                    return null;
                }
                return $originalResolver($root,$args, $context,$resolveInfo );
                }
            )
        );
    }
}

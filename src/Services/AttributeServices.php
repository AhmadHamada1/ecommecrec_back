<?php
namespace App\Services;

use App\Entities\Attribute;
use App\Entities\AttributeItem;
use App\Repository\AttributeRepository;
use App\Repository\AttributeItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\UserError;

class AttributeServices
{
    public function __construct(
        private AttributeRepository $attrRepo,
        private AttributeItemRepository $itemRepo,
        private EntityManagerInterface $em
    ) {}


    public function upsertAttributeWithItemsNoFlush(string $id, string $name, string $type, array $items): Attribute
    {
        $attr = $this->attrRepo->find($id);
        if (!$attr) {
            $attr = new Attribute();
            $attr->id = $id;
        }

        $attr->name = $name;
        $attr->type = $type;
        $this->em->persist($attr);

        // find existing items for this attribute
        $existing = $this->itemRepo->findByAttribute($attr);
        $byId = [];
        foreach ($existing as $e) {
            $byId[$e->id] = $e;
        }

        $seen = [];
        foreach ($items as $i) {
            foreach (['id','value','displayValue'] as $k) {
                if (!array_key_exists($k, $i)) {
                    throw new UserError("AttributeItem for '{$id}' missing key '{$k}'");
                }
            }

            $seen[$i['id']] = true;

            if (isset($byId[$i['id']])) {
                // update existing
                $it = $byId[$i['id']];
                $it->value        = $i['value'];
                $it->displayValue = $i['displayValue'];
            } else {
                // create new
                $it = new AttributeItem();
                $it->id           = $i['id'];
                $it->value        = $i['value'];
                $it->displayValue = $i['displayValue'];
                $it->attribute    = $attr;
                $this->em->persist($it);
            }

            // ✅ ensure every item is linked
            if (!$attr->items->contains($it)) {
                $attr->items->add($it);
            }
        }

        // remove leftovers (items that are no longer in input)
        foreach ($byId as $leftId => $leftover) {
            if (!isset($seen[$leftId])) {
                $this->em->remove($leftover);
            }
        }

        return $attr;
    }

//     public function upsertAttributeWithItemsNoFlush(string $id, string $name, string $type, array $items): Attribute
// {
//     $attr = $this->attrRepo->find($id);
//     if (!$attr) {
//         $attr = new Attribute();
//         $attr->id = $id;
//     }

//     $attr->name = $name;
//     $attr->type = $type;
//     $this->em->persist($attr);

//     // find existing items for this attribute
//     $existing = $this->itemRepo->findByAttribute($attr);
//     $byId = [];
//     foreach ($existing as $e) {
//         $byId[$e->id] = $e;
//     }

//     $seen = [];

//     foreach ($items as $i) {
//         foreach (['id', 'value', 'displayValue'] as $k) {
//             if (!array_key_exists($k, $i)) {
//                 throw new UserError("AttributeItem for '{$id}' missing key '{$k}'");
//             }
//         }

//         $itemId = $i['id'];
//         $seen[$itemId] = true;

//         if (isset($byId[$itemId])) {
//             // update existing under this attribute
//             $it = $byId[$itemId];
//             $it->value        = $i['value'];
//             $it->displayValue = $i['displayValue'];
//         } else {
//             // ✅ check globally if this AttributeItem already exists
//             $it = $this->itemRepo->find($itemId);
//             if (!$it) {
//                 // create new if not found globally
//                 $it = new AttributeItem();
//                 $it->id           = $itemId;
//                 $it->value        = $i['value'];
//                 $it->displayValue = $i['displayValue'];
//                 $this->em->persist($it);
//             }

//             // ✅ link attribute relation (if changed)
//             $it->attribute = $attr;
//         }

//         // ensure relationship
//         if (!$attr->items->contains($it)) {
//             $attr->items->add($it);
//         }
//     }

//     // remove leftovers (items no longer in input)
//     foreach ($byId as $leftId => $leftover) {
//         if (!isset($seen[$leftId])) {
//             $this->em->remove($leftover);
//         }
//     }

//     return $attr;
// }

// public function upsertAttributeWithItemsNoFlush(string $id, string $name, string $type, array $items): Attribute
// {
//     // 🔹 1. Find or create the Attribute itself
//     $attr = $this->attrRepo->find($id);
//     if (!$attr) {
//         $attr = new Attribute();
//         $attr->id = $id;
//     }

//     $attr->name = $name;
//     $attr->type = $type;
//     $this->em->persist($attr);

//     // 🔹 2. Find existing items for this specific attribute
//     $existing = $this->itemRepo->findByAttribute($attr);
//     $byId = $this->itemRepo->indexById($existing);
//     $seen = [];

//     // 🔹 3. Iterate through incoming items
//     foreach ($items as $i) {
//         foreach (['id', 'value', 'displayValue'] as $k) {
//             if (!array_key_exists($k, $i)) {
//                 throw new \GraphQL\Error\UserError("AttributeItem for '{$id}' missing key '{$k}'");
//             }
//         }

//         $itemId = $i['id'];
//         $seen[$itemId] = true;

//         if (isset($byId[$itemId])) {
//             // ✅ Item already exists under this attribute → just update its data
//             $it = $byId[$itemId];
//             $it->value = $i['value'];
//             $it->displayValue = $i['displayValue'];
//         } else {
//             // ✅ Try to find globally by ID (e.g. "Yes", "No")
//             $existingItem = $this->itemRepo->find($itemId);

//             if ($existingItem) {
//                 // ✅ Reuse existing global item
//                 $it = $existingItem;
//                 // update its values in case they changed
//                 $it->value = $i['value'];
//                 $it->displayValue = $i['displayValue'];
//             } else {
//                 // ✅ Create new one if not exist globally
//                 $it = new AttributeItem();
//                 $it->id = $itemId;
//                 $it->value = $i['value'];
//                 $it->displayValue = $i['displayValue'];
//                 $this->em->persist($it);
//             }

//             // ✅ Always attach to the current attribute
//             $it->attribute = $attr;
//         }

//         // ✅ Ensure the relationship is set
//         if (!$attr->items->contains($it)) {
//             $attr->items->add($it);
//         }

//         $this->em->persist($it);
//     }

//     // 🔹 4. Remove old items that no longer exist in the new input
//     foreach ($byId as $leftId => $leftover) {
//         if (!isset($seen[$leftId])) {
//             $this->em->remove($leftover);
//         }
//     }

//     // 🔹 5. Return the attribute (flush will be done by caller)
//     return $attr;
// }



    public function createAttributeItem(Attribute $attribute, string $displayValue, string $value, string $id): AttributeItem
    {
        $item = new AttributeItem();
        $item->id = $id;
        $item->displayValue = $displayValue;
        $item->value = $value;
        $item->attribute = $attribute;

        $this->em->persist($item);

        return $item;
    }

    public function upsertAttributeItem(Attribute $attribute, array $data): AttributeItem
{
    $id = $data['id'];
    $value = $data['value'];
    $displayValue = $data['displayValue'];

    $repo = $this->em->getRepository(AttributeItem::class);
    $item = $repo->find($id);

    if (!$item) {
        $item = new AttributeItem();
        $item->id = $id;
        $item->value = $value;
        $item->displayValue = $displayValue;
        $item->attribute = $attribute;
        $this->em->persist($item);
    }

    // ✅ Always ensure bidirectional link (new or existing)
    if (!$attribute->items->contains($item)) {
        $attribute->items->add($item);
    }

    return $item;
}

}

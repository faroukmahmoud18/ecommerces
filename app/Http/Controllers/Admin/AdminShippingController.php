                'message' => 'تم حذف منطقة الشحن بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting shipping zone: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف منطقة الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء سعر شحن جديد
     */
    public function createRate(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:shipping_zones,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'priority' => 'required|integer|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'handling_fee' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $rate = ShippingRate::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة سعر الشحن بنجاح',
                'rate' => $rate
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating shipping rate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة سعر الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث سعر شحن
     */
    public function updateRate(Request $request, ShippingRate $rate)
    {
        $request->validate([
            'zone_id' => 'required|exists:shipping_zones,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'priority' => 'required|integer|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'handling_fee' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $rate->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث سعر الشحن بنجاح',
                'rate' => $rate
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating shipping rate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث سعر الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف سعر شحن
     */
    public function deleteRate(ShippingRate $rate)
    {
        try {
            DB::beginTransaction();

            $rate->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف سعر الشحن بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting shipping rate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف سعر الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
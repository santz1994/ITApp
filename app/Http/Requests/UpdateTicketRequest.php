<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UpdateTicketRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
     public function rules()
     {
         // Check if this is an API request
         $isApi = request()->is('api/*');
         
         $rules = [
           // API: optional, Web: required
           'user_id' => $isApi ? 'sometimes|exists:users,id' : 'required|exists:users,id',
           'location_id' => $isApi ? 'sometimes|exists:locations,id' : 'required|exists:locations,id',
           
           // Status, Type, Priority: allow updates to both Web and API
           // Fixed: removed the contradictory 'sometimes|required' combination
           // Use 'sometimes|exists' to allow optional updates OR required when submitted
           'ticket_status_id' => 'nullable|sometimes|exists:tickets_statuses,id',
           'ticket_type_id' => 'nullable|sometimes|exists:tickets_types,id',
           'ticket_priority_id' => 'nullable|sometimes|exists:tickets_priorities,id',
           
           // Additional optional fields
           'subject' => 'sometimes|string|max:255',
           'title' => 'sometimes|string|max:255',
           'description' => 'sometimes|string',
           'asset_id' => 'nullable|exists:assets,id',
           'asset_ids' => 'nullable|array',
           'asset_ids.*' => 'exists:assets,id',
           'assigned_to' => 'nullable|exists:users,id',
         ];
         
         // Only super-admin can modify SLA due date
         if (auth()->check() && auth()->user()->hasRole('super-admin')) {
             $rules['sla_due'] = 'nullable|date';
         }
         
         return $rules;
     }

     /**
      * Custom error messages for fields
      * 
      * @return array
      */
     public function messages()
     {
       return [
         'user_id.required' => 'You must select an Agent',
         'user_id.exists' => 'The selected Agent is invalid',
         'location_id.required' => 'You must select a Location',
         'location_id.exists' => 'The selected Location is invalid',
         'ticket_status_id.required' => 'You must select a Ticket Status',
         'ticket_status_id.exists' => 'The selected Ticket Status is invalid',
         'ticket_type_id.required' => 'You must select a Ticket Type',
         'ticket_type_id.exists' => 'The selected Ticket Type is invalid',
         'ticket_priority_id.required' => 'You must select a Ticket Priority',
         'ticket_priority_id.exists' => 'The selected Ticket Priority is invalid',
         'title.max' => 'The title must not exceed 255 characters',
         'asset_id.exists' => 'The selected Asset is invalid',
         'assigned_to.exists' => 'The selected user to assign is invalid',
         'sla_due.date' => 'The SLA due date must be a valid date'
       ];
     }
}

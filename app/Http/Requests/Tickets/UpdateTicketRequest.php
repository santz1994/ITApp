<?php

namespace App\Http\Requests\Tickets;

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
         return [
           'user_id' => 'nullable|exists:users,id',
           'assigned_to' => 'nullable|exists:users,id',
           'location_id' => 'required|exists:locations,id',
           'ticket_status_id' => 'required|exists:tickets_statuses,id',
           'ticket_type_id' => 'required|exists:tickets_types,id',
           'ticket_priority_id' => 'required|exists:tickets_priorities,id'
         ];
     }

     /**
      * Custom error messages for fields
      * 
      * @return array
      */
     public function messages()
     {
       return [
         'user_id.exists' => 'Selected reporter user does not exist',
         'assigned_to.exists' => 'Selected agent does not exist',
         'location_id.required' => 'You must select a Location',
         'location_id.exists' => 'Selected location does not exist',
         'ticket_status_id.required' => 'You must select a Ticket Status',
         'ticket_status_id.exists' => 'Selected status does not exist',
         'ticket_type_id.required' => 'You must select a Ticket Type',
         'ticket_type_id.exists' => 'Selected type does not exist',
         'ticket_priority_id.required' => 'You must select a Ticket Priority',
         'ticket_priority_id.exists' => 'Selected priority does not exist'
       ];
     }
}

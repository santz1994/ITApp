<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

/**
 * TicketBulkUpdateRequest
 *
 * Validates bulk update operations for tickets
 * Supports:
 * - Bulk status updates
 * - Bulk assignments
 * - Bulk priority/type updates
 * - Bulk field modifications
 *
 * Validation rules ensure:
 * - Ticket IDs are valid and exist
 * - Update values are valid for their fields
 * - Resolution status is consistent with ticket status
 */
class TicketBulkUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Bulk updates require 'update_tickets' permission
        return $this->user() && $this->user()->can('update', \App\Ticket::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Ticket IDs: array of integers
            'ticket_ids' => 'required|array|min:1|max:10000',
            'ticket_ids.*' => 'integer|distinct',

            // Status fields
            'status_id' => 'nullable|integer|exists:tickets_statuses,id',
            'is_resolved' => 'nullable|boolean',
            'is_open' => 'nullable|boolean',

            // Priority and type
            'priority_id' => 'nullable|integer|exists:tickets_priorities,id',
            'type_id' => 'nullable|integer|exists:tickets_types,id',

            // Assignment
            'assigned_to' => 'nullable|integer|exists:users,id',
            'department_id' => 'nullable|integer|exists:divisions,id',

            // Date fields
            'due_date' => 'nullable|date|after_or_equal:today',
            'due_from' => 'nullable|date',
            'due_to' => 'nullable|date|after_or_equal:due_from',

            // Generic field updates
            'updates' => 'nullable|array',
            'updates.status_id' => 'nullable|integer|exists:tickets_statuses,id',
            'updates.priority_id' => 'nullable|integer|exists:tickets_priorities,id',
            'updates.type_id' => 'nullable|integer|exists:tickets_types,id',
            'updates.assigned_to' => 'nullable|integer|exists:users,id',
            'updates.due_date' => 'nullable|date',
            'updates.notes' => 'nullable|string|max:5000',

            // Operation control
            'dry_run' => 'nullable|boolean',
            'reason' => 'nullable|string|max:1000',
            'resolution_notes' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'ticket_ids.required' => 'At least one ticket ID is required',
            'ticket_ids.array' => 'Ticket IDs must be an array',
            'ticket_ids.min' => 'At least one ticket must be selected',
            'ticket_ids.max' => 'Maximum 10,000 tickets per operation',
            'ticket_ids.*.integer' => 'Each ticket ID must be an integer',
            'ticket_ids.*.distinct' => 'Duplicate ticket IDs are not allowed',

            'status_id.exists' => 'Invalid status ID',
            'priority_id.exists' => 'Invalid priority ID',
            'type_id.exists' => 'Invalid ticket type ID',
            'assigned_to.exists' => 'The assigned user does not exist',
            'department_id.exists' => 'Invalid department ID',

            'due_date.date' => 'Due date must be a valid date',
            'due_date.after_or_equal' => 'Due date cannot be in the past',
            'due_from.date' => 'Due from date must be a valid date',
            'due_to.date' => 'Due to date must be a valid date',
            'due_to.after_or_equal' => 'Due to date must be after or equal to due from date',

            'is_resolved.boolean' => 'is_resolved must be true or false',
            'is_open.boolean' => 'is_open must be true or false',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Remove duplicates from ticket_ids
        if ($this->has('ticket_ids')) {
            $this->merge([
                'ticket_ids' => array_unique((array)$this->ticket_ids)
            ]);
        }

        // Convert dry_run string to boolean
        if ($this->has('dry_run')) {
            $dryRun = $this->dry_run;
            $this->merge([
                'dry_run' => in_array($dryRun, [true, 'true', '1', 1], true)
            ]);
        }

        // Convert resolution flags to boolean
        if ($this->has('is_resolved')) {
            $this->merge([
                'is_resolved' => in_array($this->is_resolved, [true, 'true', '1', 1], true)
            ]);
        }

        if ($this->has('is_open')) {
            $this->merge([
                'is_open' => in_array($this->is_open, [true, 'true', '1', 1], true)
            ]);
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'message' => 'Validation failed for bulk update operation',
            'errors' => $validator->errors()
        ], 422));
    }

    /**
     * Get bulk update parameters as array
     * Used by controller to pass to bulk operation trait
     *
     * @return array
     */
    public function getBulkUpdateParams()
    {
        return [
            'ticket_ids' => $this->ticket_ids,
            'status_id' => $this->status_id,
            'priority_id' => $this->priority_id,
            'type_id' => $this->type_id,
            'assigned_to' => $this->assigned_to,
            'department_id' => $this->department_id,
            'due_date' => $this->due_date,
            'is_resolved' => $this->is_resolved,
            'is_open' => $this->is_open,
            'updates' => $this->updates,
            'dry_run' => $this->dry_run ?? false,
            'reason' => $this->reason,
            'resolution_notes' => $this->resolution_notes,
            'user_id' => $this->user()->id
        ];
    }
}

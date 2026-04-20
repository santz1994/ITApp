@once
@push('styles')
<style>
    .module-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
        padding: 10px 12px;
        border: 1px solid #d7e0ea;
        border-radius: 10px;
        background: #f7fafc;
    }

    .module-toolbar-user {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .module-toolbar-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid #c4d2e2;
        background: linear-gradient(135deg, #3c8dbc, #2e6c94);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .module-toolbar-user-meta {
        min-width: 0;
    }

    .module-toolbar-user-name {
        color: #1f2d3d;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.2;
    }

    .module-toolbar-user-email {
        color: #6b7a8b;
        font-size: 11px;
        line-height: 1.2;
        max-width: 320px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .module-toolbar-controls {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
        flex-wrap: wrap;
    }

    .module-language-toggle .btn {
        border-color: #c8d5e4;
        color: #3f4e5f;
        background: #ffffff;
    }

    .module-language-toggle .btn.active {
        background: #1b6ca8;
        border-color: #1b6ca8;
        color: #ffffff;
    }

    @media (max-width: 767px) {
        .module-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .module-toolbar-user {
            width: 100%;
        }

        .module-toolbar-controls {
            width: 100%;
            justify-content: flex-end;
            margin-left: 0;
        }

        .module-toolbar-user-email {
            max-width: 100%;
        }
    }
</style>
@endpush
@endonce

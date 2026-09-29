import React from 'react';
import { createRoot } from 'react-dom/client';
import CourseCatalog from './catalog/CourseCatalog';

const el = document.getElementById('course-catalog');

if (el) {
    const props = JSON.parse(el.dataset.props || '{}');
    createRoot(el).render(
        <React.StrictMode>
            <CourseCatalog {...props} />
        </React.StrictMode>
    );
}

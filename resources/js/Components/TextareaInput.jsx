import { forwardRef } from 'react';

export default forwardRef(function TextareaInput(
    { className = '', ...props },
    ref,
) {
    return (
        <textarea
            {...props}
            className={
                'rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ' +
                className
            }
            ref={ref}
        />
    );
});

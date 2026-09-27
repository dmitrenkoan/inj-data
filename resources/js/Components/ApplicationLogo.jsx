export default function ApplicationLogo({ className, ...props }) {
    return (
        <img
            src="/images/logo.png"
            alt="Логотип"
            className={className}
            {...props}
        />
    );
}

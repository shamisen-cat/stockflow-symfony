declare module '@symfony/stimulus-bundle' {
    interface StimulusApplication {
        debug: boolean;
        register(name: string, controller: unknown): void;
    }

    export function startStimulusApp(): StimulusApplication;

    export function loadControllers(
        application: StimulusApplication,
        eagerControllers: Record<string, unknown>,
        lazyControllers: Record<string, () => Promise<{ default: unknown }>>,
    ): void;
}

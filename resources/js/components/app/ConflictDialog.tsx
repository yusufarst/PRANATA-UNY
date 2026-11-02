import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import type { Command } from '@/lib/command';

/**
 * The version conflict of a command (IX-002): someone else saved first, so nothing of this input was saved. The main step
 * loads the latest data; closing keeps the input on screen to copy from.
 */
export function ConflictDialog({
    command,
}: {
    command: Pick<Command, 'conflict' | 'dismissConflict' | 'reloadLatest'>;
}) {
    const { t } = useTranslation();

    return (
        <Dialog
            open={command.conflict !== null}
            onOpenChange={(open) => !open && command.dismissConflict()}
        >
            <DialogContent>
                <DialogTitle>{t('common:conflict.title')}</DialogTitle>
                <DialogDescription>
                    {command.conflict ?? t('common:conflict.description')}
                </DialogDescription>
                <p className="text-sm text-muted-foreground">
                    {t('common:conflict.unsaved')}
                </p>
                <DialogFooter className="gap-2">
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={command.dismissConflict}
                    >
                        {t('common:conflict.keep')}
                    </Button>
                    <Button
                        type="button"
                        onClick={command.reloadLatest}
                        data-test="conflict-reload-button"
                    >
                        {t('common:conflict.reload')}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

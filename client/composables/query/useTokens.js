import { useQueryClient, useQuery, useMutation } from '@tanstack/vue-query'
import { tokensApi } from '~/api/tokens'

export function useTokens() {
  const queryClient = useQueryClient()

  // Abilities configuration (moved from store)
  const abilities = [
    {
      title: 'Gerenciar integrações',
      name: 'manage-integrations',
    },
    {
      title: 'Formulários – Leitura',
      name: 'forms-read',
    },
    {
      title: 'Formulários – Escrita',
      name: 'forms-write',
    },
    {
      title: 'Espaços de trabalho – Leitura',
      name: 'workspaces-read',
    },
    {
      title: 'Espaços de trabalho – Escrita',
      name: 'workspaces-write',
    },
    {
      title: 'Usuários do espaço – Leitura',
      name: 'workspace-users-read',
    },
    {
      title: 'Usuários do espaço – Escrita',
      name: 'workspace-users-write',
    },
  ]

  const getAbility = (name) => {
    return abilities.find((ability) => ability.name === name) ?? {
      name,
      title: name,
    }
  }

  // Queries
  const list = (options = {}) => {
    return useQuery({
      queryKey: ['tokens', 'list'],
      queryFn: () => tokensApi.list(options),
      onSuccess: (data) => {
        data?.forEach(token => {
          queryClient.setQueryData(['tokens', token.id], token)
        })
      },
      ...options
    })
  }

  const detail = (tokenId, options = {}) => {
    return useQuery({
      queryKey: ['tokens', tokenId],
      queryFn: () => {
        // Since there's no individual get endpoint, we get from the cached list
        const cachedTokens = queryClient.getQueryData(['tokens', 'list'])
        return cachedTokens?.find(t => t.id === tokenId) || null
      },
      enabled: !!tokenId,
      ...options
    })
  }

  // Mutations
  const create = (options = {}) => {
    return useMutation({
      mutationFn: (data) => tokensApi.create(data),
      // Creation returns a one-time secret, not a token metadata resource.
      onSuccess: () => queryClient.invalidateQueries({ queryKey: ['tokens', 'list'] }).then(() => {
        useAlert().success('Token criado')
      }),
      ...options
    })
  }

  const remove = (options = {}) => {
    return useMutation({
      mutationFn: (tokenId) => tokensApi.delete(tokenId),
      onSuccess: (data, deletedTokenId) => {
      // Built-in cache management
      queryClient.removeQueries({ queryKey: ['tokens', deletedTokenId] })
      
      // Remove from list query data if loaded
      const currentList = queryClient.getQueryData(['tokens', 'list'])
      if (currentList) {
        queryClient.setQueryData(
          ['tokens', 'list'],
          currentList.filter(token => token.id != deletedTokenId) // Use != for loose equality
        )
      }
      useAlert().success('Token excluído')
      },
      ...options
    })
  }

  const invalidateAll = () => {
    queryClient.invalidateQueries({ queryKey: ['tokens'] })
  }

  const getTokenById = (tokenId) => {
    return queryClient.getQueryData(['tokens', tokenId])
  }

  const getTokenByName = (name) => {
    const tokens = queryClient.getQueryData(['tokens', 'list'])
    if (Array.isArray(tokens)) {
      return tokens.find(t => t.name === name) || null
    }
    if (tokens?.data) {
      return tokens.data.find(t => t.name === name) || null
    }
    return null
  }

  return {
    // Queries
    list,
    detail,
    
    // Mutations
    create,
    remove,
    
    // Utilities
    invalidateAll,
    getTokenById,
    getTokenByName,
    
    // Abilities (moved from store)
    abilities,
    getAbility
  }
}
